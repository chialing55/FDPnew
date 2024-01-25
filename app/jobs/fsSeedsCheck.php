<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;
 
class fsSeedsCheck
{
	public function check($record, $spinfo, $type, $type2){


		$checknote='';
		// 一筆一筆檢查
		//基本檢查

		$trap=intval($record['trap']);

		if ($trap>107 || $trap<1 || $trap==42){
			$checknote='Trap 不正確';

		} else if ($record['count']==0){

		//1. if count = 0
		
			$checknote='數量 不得為 0。';			
		} else {
		//2 csp=''
			if ($record['csp']==''){
			$checknote='種類 不得為 空白。';		
			} else {
			//3 code = 0
				if ($record['code']=='0'){
					$checknote='類別 不得為 0。';	 
				} else if ($record['code']=='1') {
				// 3 code = 1
				// a seeds = 0
					if ($spinfo[$record['csp']]['size']=='B'){
						if ($record['seeds']=='0'){
						$checknote='種子數 不得為 0。';						
						} else if ($record['seeds'] < $record['count']) {
					// a seeds < count
							$checknote='種子數不應小於數量。';	
						} else {
					// b viability = ''
							if ($record['viability'] == '' ){
								$checknote='活性 不得為 空白。';							
							} else {
								if ($record['csp'] == '九芎' or $record['csp'] == '凹葉越橘' or $record['csp'] == '五節芒' or $record['csp'] == 'UNKCOM1' or $record['csp'] == 'UNKCOM2' or $record['csp'] == 'UNKCOM3'){
										if ($record['viability'] != 'NA' ){
										$checknote='活性應為 NA。';		
										}	
										if ($record['seeds'] == 'NA' ){
										$checknote='種子數不應為 NA。';		
										}
									
								} else {
									
									if ($record['viability'] == 'NA' ){
									$checknote='活性不應為 NA。';		
									}
									if ($record['seeds'] == 'NA' ){
									$checknote='種子數不應為 NA。';		
									}
									if ($record['viability'] > $record['seeds'] ){
									$checknote='活性數不應大於種子數。';		
									}
								}

							}
						}
					} else {  //小種子
						if ($record['viability'] != 'NA' ){
						$checknote='活性應為 NA。';		
						}
						if ($record['seeds'] != 'NA' ){
						$checknote='種子數應為 NA。';		
						}
					}

				// f fragments = ''

					if ($record['fragments'] != '' && $record['fragments'] != '0'){
							$checknote='碎片3數量應為 空白/0。';
						} else {
							$record['fragments']='0';
					}
				//g sex l!= ''	
					if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
					}	
				} else if ($record['code']=='2') {
				// 3 code = 2
				// a seeds = 0		
					if ($record['seeds']=='0'){
						$checknote='種子數不得為 0。';						
					} else if ($record['seeds'] != $record['count']) {
				// a seeds != count
						$checknote='種子數應等於數量。';	
					} else {
						// c  size =B, viability !=NA	
						if ($record['viability'] == '' ){
							$checknote='活性 不得為 空白。';							
						} else {   //會收到種子的應皆為大種子的種類

							if ($record['csp'] == '九芎' or $record['csp'] == '凹葉越橘' or $record['csp'] == '五節芒' or $record['csp'] == 'UNKCOM1' or $record['csp'] == 'UNKCOM2' or $record['csp'] == 'UNKCOM3'){
								if ($record['viability'] != 'NA' ){
								$checknote='活性應為 NA。';		
								}	
								if ($record['seeds'] == 'NA' ){
								$checknote='種子數不應為 NA。';		
								}

							} else {

								if ($record['viability'] == 'NA' ){
								$checknote='活性不應為 NA。';		
								}
								if ($record['seeds'] == 'NA' ){
								$checknote='種子數不應為 NA。';		
								}
								if ($record['viability'] > $record['seeds'] ){
								$checknote='活性數不應大於種子數。';		
								}
							}

						}
					// f fragments = ''
						if ($record['fragments'] != '' && $record['fragments'] != '0'){
							$checknote='碎片3數量應為 空白/0。';
						} else {
							$record['fragments']='0';
						}	
					//g sex l!= ''	
						if ($record['sex'] != ''){
							$checknote='性別欄位應為 空白。';
						}
					}	
				} else if ($record['code']=='3') {
				// 3 code = 3
				// a seeds != 0	
					// if ($record['seeds']==''){
					// 	$record['seeds']='0';
					// }
					if ($record['seeds']!='' && $record['seeds'] != '0'){
						$checknote='種子數應為 空白/0。';						
					} else {
						$record['seeds'] = '0';
					}
				// b viability != ''		
					if ($record['viability']!='' && $record['viability'] != '0'){
						$checknote='活性應為 空白/0。';						
					} else {
						$record['viability'] = '0';
					}
				// f fragments = 0
					if ($record['fragments'] == ''){
						$checknote='碎片3數量不得為空白。';
					}	else if ($record['fragments'] > $record['count']){
					// 	f fragments > count
						$checknote='碎片3數量不應大於數量。';
					}
				//g sex l!= ''	
					if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
					}	
				} else if ($record['code']=='4') {
				// 3 code = 4
				// a seeds != 0	
					// if ($record['seeds']==''){
					// 	$record['seeds']='0';
					// }
					if ($record['seeds']!='' && $record['seeds'] != '0'){
						$checknote='種子數應為 空白/0。';						
					} else {
						$record['seeds'] = '0';
					} 
				// b viability != ''		
					if ($record['viability']!='' && $record['viability'] != '0'){
						$checknote='活性應為 空白/0。';						
					} else {
						$record['viability'] = '0';
					} 
				// c count != '1'		
					if ($record['count']!='1'){
						$checknote='數量應為 1。';						
					} 	
				// f fragments = ''
					if ($record['fragments'] != '' && $record['fragments'] != '0'){
							$checknote='碎片3數量應為 空白/0。';
						} else {
							$record['fragments']='0';
					}	
				//g sex l!= ''	
					if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
					}	
				} else if ($record['code']=='5') {
				// 3 code = 5
				// a seeds != 0		
					// if ($record['seeds']==''){
					// 	$record['seeds']='0';
					// }
					if ($record['seeds']!='' && $record['seeds'] != '0'){
						$checknote='種子數應為 空白/0。';						
					} else {
						$record['seeds'] = '0';
					} 
				// b viability != ''		
					if ($record['viability']!='' && $record['viability'] != '0'){
						$checknote='活性應為 空白/0。';						
					} else {
						$record['viability'] = '0';
					} 	
				// f fragments = ''
					if ($record['fragments'] != '' && $record['fragments'] != '0'){
							$checknote='碎片3數量應為 空白/0。';
						} else {
							$record['fragments']='0';
					}	
				//g sex l!= ''	
					if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
					}	
				} else if ($record['code']=='6') {
				// 3 code = 6
				// a seeds != 0	
					// if ($record['seeds']==''){
					// 	$record['seeds']='0';
					// }
					if ($record['seeds']!='' && $record['seeds'] != '0'){
						$checknote='種子數應為 空白/0。';						
					} else {
						$record['seeds'] = '0';
					} 
				// b viability != ''		
					if ($record['viability']!='' && $record['viability'] != '0'){
						$checknote='活性應為 空白/0。';						
					} else {
						$record['viability'] = '0';
					} 	
				// c count != '1'		
					if ($record['count']!='1'){
						$checknote='數量應為 1。';						
					} 
					// f fragments = ''
					if ($record['fragments'] != '' && $record['fragments'] != '0'){
							$checknote='碎片3數量應為 空白/0。';
						} else {
							$record['fragments']='0';
					}	
				//d csp 


					if ($record['csp']=='長葉木薑子'){
						if ($record['sex'] == ''){
						$checknote='種類為長葉木薑子，性別欄位不得為 空白。';
						}
					} else {
						if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
						}
					}
				} 
			}
		}

	// 特殊檢查	
	//  csp = 烏+長
			if ($record['csp'] == '栲屬' || $record['csp'] == '薹屬'){
				if ($record['code'] != '6'){
					$checknote='類別欄位應為 6。';
				}
			}
	// trap+種類+類別 不能一樣	
			$checksign=$record['census'].$record['trap'].$record['csp'].$record['code'];
			$checkarray=[];
			if ($type2=='record'){
				if ($type=='n'){
					$dataexit=FsSeedsRecord1::query()->get()->toArray();
				} else {
					$dataexit=FsSeedsRecord1::where('id', 'not like', $record['id'])->get()->toArray();
				}
			} else {
				if ($type=='n'){
					$dataexit=FsSeedsFulldata::where('census', 'like', $record['census'])->get()->toArray();
				} else {
					$dataexit=FsSeedsFulldata::where('census', 'like', $record['census'])->where('id', 'not like', $record['id'])->get()->toArray();
				}				
			}

			if (count($dataexit)>0){
				foreach ($dataexit as $data){
					$checkarray[]=$data['census'].$data['trap'].$data['csp'].$data['code'];
				}

				if (in_array($checksign, $checkarray)){
					$checknote='重複。';
				} 
			}

			// if ($checknote ==''){$checknote='確 。';}

		// return $checknote;

            return [
                'result' => $record,
                'checknote' => $checknote,
                // 'type2' => $type2

            ];

	}
}


