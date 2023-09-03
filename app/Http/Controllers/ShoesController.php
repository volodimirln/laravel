<?php

namespace App\Http\Controllers;

use App\Model\Gender;
use App\Model\Shoes as Shoes;
use App\Model\Type;
use App\Model\Brands;
use App\Model\Materials;
use App\Model\Season;
use App\Model\Size;

use Illuminate\Http\Request;
use LDAP\Result;

class ShoesController extends Controller
{



    public function getShoesJSON(Request $request){
        $sortField = "";
        $sortType = "";
        $sortField = (isset($request->sortField)) ? $sortField =  $request->sortField: $sortField = "popularity";
        $sortType = ($request->sortType == 1) ? $sortType =  "desc" : $sortType = "asc";

        $result = Shoes::orderBy($sortField, $sortType);
        $result = (isset($request->offsetRec)) ? $result->offset($request->offsetRec) : $result;
        $result = (isset($request->limitRec)) ? $result->limit($request->limitRec) : $result;
        $result = $result->get();

        $result = (isset($request->gender)) ? $result->where("genderId", "=", $request->gender) : $result;

        echo json_encode($this->getResult($result, 1, 200), JSON_UNESCAPED_UNICODE);
    }

//  Size.size BETWEEN {$sizeUp} and {$sizeDown} GROUP BY Shoes.id ORDER BY {$getParSort} {$getTypeSort}  {$limit} ";

    public function getShoesFilterJSON(Request $request){
        $offsetRec = $request->offsetRec;
        $limitRec = $request->limitRec;
        $sortField = "";
        $sortType = "";
        $sortField = (isset($request->sortField)) ? $sortField =  $request->sortField: $sortField = "popularity";
        $sortType = ($request->sortType == 1) ? $sortType =  "desc" : $sortType = "asc";

        $result = Shoes::orderBy($sortField, $sortType);
        $size = Size::all();

        $result = (isset($offsetRec)) ? $result->offset($offsetRec) : $result;
        $result = (isset($limitRec)) ? $result->limit($limitRec) : $result;
        $result = $result->get();
        
        $result = (isset($request->gender)) ? $result->where("genderId", "=", $request->gender) : $result;
        $result = (isset($request->markdown)) ? $result->where("markdown", "=", $request->markdown) : $result;
        $result = (isset($request->markdown)) ? $result->where("markdown", "=", $request->markdown) : $result;
        $result = (isset($request->brand)) ? $result->where("brand", "=", $request->brand) : $result;
        $result = (isset($request->season)) ? $result->where("seasonId", "=", $request->season) : $result;
        $result = (isset($request->typeShoes)) ? $result->where("typeId", "=", $request->typeShoes) : $result;
        $result = (isset($request->materials)) ? $result->where("materials", "=", $request->materials) : $result;
        $result = (isset($request->outmaterial)) ? $result->where("outmaterial", "=", $request->outmaterial) : $result;


        $result = (isset($request->priceUp) && isset($request->priceDown)) ? $result->whereBetween("price", [$request->priceUp, $request->priceDown]) : $result;
        $result = (isset($request->discountUp) && isset($request->discountDown)) ? $result->whereBetween("discount", [$request->discountUp, $request->discountDown]) : $result;
        

        // if(isset($request->sizeUp) && isset($request->sizeDown)){
        //     $size = $size->whereBetween('size', [$request->sizeUp, $request->sizeDown]);
        //     foreach($result as $value){

        //         $result = $size->where("shoesId", "=", $value->id);
        //     }
        //     echo json_encode($result, JSON_UNESCAPED_UNICODE);;
        // }

        echo json_encode($this->getResult($result, $request->sizeUp, $request->sizeDown), JSON_UNESCAPED_UNICODE);
    }

    protected function getResult($array, $sizeUp, $sizeDown){
        $formattedResult = [];
        $size = Size::all();

        foreach($array as $value){
            $id = $value->id;
            $rt = $size->where("shoesId", "=", $id)->whereBetween('size', [$sizeUp, $sizeDown])->count();
            if((int)$rt > 0)
            $formattedResult[] = array(
                "id" => $value->id,
                "vendorCode" => $value->vendorCode,
                "typeId" => $value->typeId,
                "seasonId" => $value->seasonId,
                "brand" => $value->brand,
                "brandId" => $value->brandId,
                "genderId" => $value->genderId,
                "insoleMaterial" => $value->insoleMaterial,
                "description" => $value->description,
                "price" => $value->price,
                "markdown" => $value->markdown,
                "material" => $value->materials,
                "outmaterial" => $value->outmaterial,
                "realdiscount" => $value->discount,
                "popularity" => $value->popularity,
                "timeToAdd" => $value->timeToAdd,
                "title" => $this->getType($value->typeId). " ". $this->getBrand($value->brand),
                "brands"=>$this->getBrand($value->brand),
                "discount" => round($value->discount * 100),
                "season" => $this->getSeason($value->seasonId),
                "type" => $this->getType($value->typeId),
                "gender" => $this->getGender($value->genderId),
                "materials" => $this->getMaterial($value->materials)
            );
        
    }
        return $formattedResult;
    }

    protected function getGender($id){
        $result = Gender::where('id', $id)->first();
        return $result->{"gender"};
    }

    protected function getType($id){
        $result = Type::where('id', $id)->first();
        return $result->{"title"};
    }

    protected function getBrand($id){
        $result = Brands::where('id', $id)->first();
        return $result->{"title"};
    }
    protected function getSeason($id){
        $result = Season::where('id', $id)->first();
        return $result->{"title"};
    }
    protected function getMaterial($id){
        $result = Materials::where('id', $id)->first();
        return $result->{"title"};
    }
    
}
