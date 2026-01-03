<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JusoPopupController extends Controller
{
  public function show(Request $request)
  {
    $inputYn = $request->input('inputYn', 'N');
    $addr = $request->only([
      'roadFullAddr',
      'roadAddrPart1',
      'addrDetail',
      'roadAddrPart2',
      'engAddr',
      'jibunAddr',
      'zipNo',
      'admCd',
      'rnMgtSn',
      'bdMgtSn',
      'detBdNmList',
      'bdNm',
      'bdKdcd',
      'siNm',
      'sggNm',
      'emdNm',
      'liNm',
      'rn',
      'udrtYn',
      'buldMnnm',
      'buldSlno',
      'mtYn',
      'lnbrMnnm',
      'lnbrSlno',
      'emdNo'
    ]);

    return view('juso-popup', compact('inputYn', 'addr'));
  }
}
