<?php

namespace App\Utilities;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpParser\Node\Arg;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use DateTime;

class Util
{
    public static function getLastId($id){
        return $id + 1;
    }

    public static function getDataExcel($file){
        $data = Array();
        if ($file) {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
        }
        return $data;
    }

    public static function getAgeCurrentAndBirth($birthday){
        $age = 0;
        if($birthday != null){
            $birth = DateTime::createFromFormat('Y-m-d', $birthday);
            $birthDate = new DateTime($birth->format('Y-m-d'));
            $currentDate = new DateTime();
            $age = $currentDate->diff($birthDate)->y;
        }
        return $age;
    }

    public static function getYear($date){
        $year = 0;
        if($date != null){
            $birth = DateTime::createFromFormat('Y-m-d', $date);
            $year = new DateTime($birth->format('Y'));
        }
        return $year;
    }

    public function downloadFileAction($filePath,$fileName)
    {
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        return $response;
    }
}