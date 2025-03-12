<?php

namespace App\Http\Traits;

use Exception;
use App\Models\Client;
use App\Models\Status;
use Illuminate\Support\Facades\Log;
use App\Imports\ContactsImportCheck;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

trait CommonTrait
{
    function errorIfAdminAndNotAssignee($assgineeId)
    {
        if (!Auth::user()->hasRole('user') && !$assgineeId) {
            throw new Exception('Please choose user');
        }
    }

    function getStatusId($module, $slug)
    {
        $status = Status::where('module', $module)->where('slug', $slug)->first();

        return $status->id;
    }

    function getStatusBySlug($module, $slug)
    {
        $status = Status::where('module', $module)->where('slug', $slug)->first();

        return $status;
    }

    function getStatusByName($module, $name)
    {
        $status = Status::where('module', $module)->where('name', $name)->first();

        return $status;
    }

    function checkExcelSheetData($file)
    {
        // Create an instance of the import class
        $import = new ContactsImportCheck();
        // Import the file
        Excel::import($import, $file);
        // Get the imported data
        $data = $import->getData();

        $this->checkColumnsValidity($data);
        // Check if the file has rows other than the header
        if ($import->getRowCount() < 1) {
            throw new Exception('The imported file is empty');
        }
    }

    function checkColumnsValidity($contacts)
    {
        $expectedColumns = contactColumns();

        // Check the columns for the first contact
        $firstContact = reset($contacts);
        $contactColumns = is_array($firstContact) ? array_keys($firstContact) : get_object_vars($firstContact);

        // Check for differences between expected and actual columns
        if (array_diff($expectedColumns, $contactColumns) || array_diff($contactColumns, $expectedColumns)) {
            throw new Exception("Invalid File Columns");
        }
    }
}
