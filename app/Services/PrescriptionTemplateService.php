<?php

namespace App\Services;

use App\Models\PrescriptionTemplate;
use Illuminate\Support\Str;

class PrescriptionTemplateService
{

    public function create(array $data)
    {

        return PrescriptionTemplate::create([

            'name'=>$data['name'],

            'slug'=>Str::slug($data['name']).'-'.time(),

            'paper_size'=>$data['paper_size'],

            'html'=>$data['html'],

            'css'=>$data['css'],

            'status'=>$data['status'],

            'created_by'=>auth()->id()

        ]);

    }



    public function update(
        PrescriptionTemplate $template,
        array $data
    )
    {

        $template->update([

            'name'=>$data['name'],

            'paper_size'=>$data['paper_size'],

            'html'=>$data['html'],

            'css'=>$data['css'],

            'status'=>$data['status']

        ]);

        return $template;

    }

}