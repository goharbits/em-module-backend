<?php

namespace App\Repositories\V1\Admin\Status;

use App\Http\Traits\CommonTrait;
use App\Models\Status;
use Exception;
use Illuminate\Http\Request;

class StatusRepository
{
    use CommonTrait;

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $statuses = Status::query();

        if ($request->search) {
            $statuses->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->module) {
            $statuses->where('module', $request->module);
        }

        $total = $statuses->count();

        if (isset($request->page) && $request->page > 0) {
            $statuses = $statuses->paginate($per_page);
        } else {
            $statuses = $statuses->get();
        }

        return [
            'total' => $total,
            'statuses' => $statuses,
        ];
    }

    public function get($status_id)
    {
        $status = Status::find($status_id);

        return $status;
    }

    public function store(Request $request)
    {
        if (Status::where('name', $request->name)->where('module', $request->module)->first()) {
            throw new Exception('Status has already been taken');
        }

        $status = Status::create([
            'name' => $request->name,
            'slug' => createSlug($request->name),
            'module' => $request->module,
        ]);

        return $status;
    }

    public function update(Request $request)
    {
        $status = Status::find($request->id);

        if ($status->name != $request->name && Status::where('name', $request->name)->where('module', $request->module)->first()) {
            throw new Exception('Status has already been taken');
        }

        $status->update([
            'name' => $request->name,
            'module' => $request->module,
        ]);

        return $status;
    }
}
