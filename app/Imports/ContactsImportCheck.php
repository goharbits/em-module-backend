<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImportCheck implements ToCollection, WithHeadingRow
{
    protected $data = [];
    protected $rowCount = 0;

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $this->data = $collection->toArray();
        $this->rowCount = count($this->data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }
}
