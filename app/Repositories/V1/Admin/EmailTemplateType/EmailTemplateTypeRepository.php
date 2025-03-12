<?php

namespace App\Repositories\V1\Admin\EmailTemplateType;

use App\Http\Traits\CommonTrait;
use App\Models\EmailTemplateType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailTemplateTypeRepository
{
    use CommonTrait;

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $template_type = EmailTemplateType::query();

        if ($request->search) {
            $template_type->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $total = $template_type->count();

        if (isset($request->page) && $request->page > 0) {
            $template_type = $template_type->with('status')->paginate($per_page);
        } else {
            $template_type = $template_type->get();
        }

        return [
            'total' => $total,
            'template_type' => $template_type,
        ];
    }

    public function get($type_id)
    {
        $type = EmailTemplateType::with('status')->find($type_id);

        return $type;
    }

    public function store(Request $request)
    {
        $type = EmailTemplateType::create([
            'name' => $request->name,
            'status_id' => $request->status_id,
        ]);

        return $type;
    }

    public function update(Request $request)
    {
        $type = EmailTemplateType::find($request->id);

        if ($type->name != $request->name && EmailTemplateType::where('name', $request->name)->first()) {
            throw new Exception('Email Template Type has already been taken');
        }

        $type->update([
            'name' => $request->name,
            'status_id' => $request->status_id,
        ]);

        return $type;
    }
}
