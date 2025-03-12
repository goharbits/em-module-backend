<?php

namespace App\Repositories\V1\User\Analytics;

use Exception;
use App\Models\Lead;
use App\Models\User;
use App\Models\Email;
use App\Models\Client;
use App\Models\Status;
use App\Models\Contact;
use App\Models\Segment;
use App\Models\Campaign;
use App\Models\ContactList;
use Illuminate\Http\Request;
use App\Http\Traits\CommonTrait;
use Illuminate\Support\Facades\Auth;

class AnalyticsRepository
{
    use CommonTrait;

    public function get(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $leads = Lead::query();
        $segments = Segment::query();
        $lists = ContactList::query();

        if (isUser() || $request->user_id) {
            $user_id = $request->user_id ?? Auth::id();

            $leads->where(function ($query) use ($user_id) {
                $query->where('created_by', $user_id)
                    ->orWhere('assigned_to', $user_id);
            });

            $segments->where(function ($query) use ($user_id) {
                $query->where('created_by', $user_id)
                    ->orWhere('assigned_to', $user_id);
            });

            $lists->where(function ($query) use ($user_id) {
                $query->where('created_by', $user_id)
                    ->orWhere('assigned_to', $user_id);
            });
        }

        if ($request->created_by) {
            $leads->where('created_by', $request->created_by);
            $segments->where('created_by', $request->created_by);
            $lists->where('created_by', $request->created_by);
        }
        if ($request->assigned_to) {
            $leads->where('assigned_to', $request->assigned_to);
            $segments->where('assigned_to', $request->assigned_to);
            $lists->where('assigned_to', $request->assigned_to);
        }

        if (isAdmin()) {
            $data['total_users'] = User::where('user_type', 'user')->count();
            $data['total_admin_users'] = User::where('user_type', 'admin')->count();
        }

        $data["total_leads"] = $leads->count();
        $data["total_segments"] = $segments->count();
        $data["total_lists"] = $lists->count();

        $data["total_clients"] = Client::count();
        $data["total_contacts"] = Contact::count();

        $leadsIds = $leads->pluck('id')->toArray();
        $segmentsIds = $segments->pluck('id')->toArray();
        $listsIds = $lists->pluck('id')->toArray();

        $campaigns = Campaign::whereIn('lead_id', $leadsIds)->orWhereIn('segment_id', $segmentsIds)->orWhereIn('list_id', $listsIds);

        $campaignsData = $this->getCampaignsData($campaigns);

        // Merge the fetched campaign-related data with the existing $data array
        $data = array_merge($data, $campaignsData);

        if (isset($request->page) && $request->page > 0) {
            $data['leads'] = $leads->withCount('campaigns')->with('created_by_user', 'assigned_to_user', 'campaigns')->paginate($per_page);
            $data['segments'] = $segments->withCount('campaigns')->with('created_by_user', 'assigned_to_user', 'campaigns')->paginate($per_page);
            $data['lists'] = $lists->withCount('campaigns')->with('created_by_user', 'assigned_to_user', 'campaigns')->paginate($per_page);
        } else {
            $data['leads'] = $leads->withCount('campaigns')->with('created_by_user', 'assigned_to_user', 'campaigns')->get();
            $data['segments'] = $segments->withCount('campaigns')->with('created_by_user', 'assigned_to_user', 'campaigns')->get();
            $data['lists'] = $lists->withCount('campaigns')->with('created_by_user', 'assigned_to_user', 'campaigns')->get();
        }

        return $data;
    }

    public function getLeadAnalytics(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $lead = Lead::where('id', $request->lead_id)->first();

        $campaigns = Campaign::where('lead_id', $lead->id)->withCount('emails');

        $data = $this->getCampaignsData($campaigns);

        if (isset($request->page) && $request->page > 0) {
            $data['campaigns'] = $campaigns->withCount('emails')->with('emails', 'status')->paginate($per_page);
        } else {
            $data['campaigns'] = $campaigns->withCount('emails')->with('emails', 'status')->get();
        }

        return $data;
    }
    public function getSegmentAnalytics(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $segment = Segment::where('id', $request->segment_id)->first();

        $campaigns = Campaign::where('segment_id', $segment->id);

        $data = $this->getCampaignsData($campaigns);

        if (isset($request->page) && $request->page > 0) {
            $data['campaigns'] = $campaigns->withCount('emails')->with('emails', 'status')->paginate($per_page);
        } else {
            $data['campaigns'] = $campaigns->withCount('emails')->with('emails', 'status')->get();
        }

        return $data;
    }
    public function getListAnalytics(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $list = ContactList::where('id', $request->list_id)->first();

        $campaigns = Campaign::where('list_id', $list->id);

        $data = $this->getCampaignsData($campaigns);

        if (isset($request->page) && $request->page > 0) {
            $data['campaigns'] = $campaigns->withCount('emails')->with('emails', 'status')->paginate($per_page);
        } else {
            $data['campaigns'] = $campaigns->withCount('emails')->with('emails', 'status')->get();
        }

        return $data;
    }

    public function getCampaignAnalytics(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $campaign = Campaign::where('id', $request->campaign_id)->withCount('emails')->first();

        $emails = Email::query();

        $emails->where('campaign_id', $campaign->id);

        $data = $this->getCampaignsData($campaign, false);

        $opened_emails = $request->input('opened_emails') === 'true' ? true : false;
        $click_link_emails = $request->input('click_link_emails') === 'true' ? true : false;
        $not_opened_emails = $request->input('not_opened_emails') === 'true' ? true : false;
        $not_click_link_emails = $request->input('not_click_link_emails') === 'true' ? true : false;

        if ($opened_emails) {
            $emails->where('is_opened', true);
        }
        if ($click_link_emails) {
            $emails->where('is_link_clicked', true);
        }
        if ($not_opened_emails) {
            $emails->where('is_opened', false);
        }
        if ($not_click_link_emails) {
            $emails->where('is_link_clicked', false);
        }

        if (isset($request->page) && $request->page > 0) {
            $data['emails'] = $emails->with('client', 'contact', 'created_by_user', 'assigned_to_user')->paginate($per_page);
        } else {
            $data['emails'] = $emails->with('client', 'contact', 'created_by_user', 'assigned_to_user')->get();
        }

        return $data;
    }

    public function getCampaignsData($campaigns, $is_multiple = true)
    {

        if ($is_multiple) {
            // Assuming $campaigns is an Eloquent query builder instance
            $originalCampaigns = $campaigns->get();

            $data["total_campaigns"] = $originalCampaigns->count();
            $statuses = Status::where('module', 'campaign')->get();
            foreach ($statuses as $status) {
                $data['total_'. $status->slug .'_campaigns'] = $originalCampaigns->where('status_id', $status->id)->count();
            }

            $data["total_emails_sent"] = $originalCampaigns->sum('emails_count');
            $data["total_opened_emails"] = $originalCampaigns->filter(function ($campaign) {
                return $campaign->emails->where('is_opened', true)->count() > 0;
            })->count();
            $data["total_not_opened_emails"] = $originalCampaigns->filter(function ($campaign) {
                return $campaign->emails->where('is_opened', false)->count() > 0;
            })->count();
            $data["total_click_link_emails"] = $originalCampaigns->filter(function ($campaign) {
                return $campaign->emails->where('is_link_clicked', true)->count() > 0;
            })->count();
            $data["total_not_click_link_emails"] = $originalCampaigns->filter(function ($campaign) {
                return $campaign->emails->where('is_link_clicked', false)->count() > 0;
            })->count();
        } else {
            $data["total_emails_sent"] = $campaigns->emails_count;
            $data["total_opened_emails"] = $campaigns->emails->where('is_opened', true)->count();
            $data["total_not_opened_emails"] = $campaigns->emails->where('is_opened', false)->count();
            $data["total_click_link_emails"] = $campaigns->emails->where('is_link_clicked', true)->count();
            $data["total_not_click_link_emails"] = $campaigns->emails->where('is_link_clicked', false)->count();
        }


        return $data;
    }
}
