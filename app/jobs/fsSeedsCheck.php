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
	public function check($record, $spinfo, $type){


		$checknote='';
		// 一筆一筆檢查
		//基本檢查

		$trap=intval($record['trap']);

		if ($trap>107 || $trap<1){
			$checknote='Trap 不正確';

		} elseif ($record['count']==0){

		//1. if count = 0
		
			$checknote='數量 不得為 0。';			
		} else {
		//2 csp=''
			if ($record['csp']==''){
			$checknote='種類 不得為 空白。';		
			} else {
			//3 code = 0
				if ($record['code']=='0'){
					$checknote='code 不得為 0。';	
				} else if ($record['code']=='1') {
				// 3 code = 1
				// a seeds = 0		
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
						// c  size =B, viability !=NA	
							if (isset($spinfo[$record['csp']])){
								if ($spinfo[$record['csp']]['size']=='B'){
									
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
									}}
									
								} else if ($spinfo[$record['csp']]['size']=='S'){	
									if ($record['viability'] != 'NA' ){
									$checknote='活性應為 NA。';		
									}
									if ($record['seeds'] != 'NA' ){
									$checknote='種子數應為 NA。';		
									}							
								}
							} else {

								// $checknote='種類名稱不在名錄中，請通知管理員';
							}					
						}
						
					} 
				// f fragments = ''
					if ($record['fragments'] != ''){
						$checknote='碎片數應為 空白。';
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
						} else {
							if ($spinfo[$record['csp']]['size']=='B'){
								
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
								}}
								
							} else if ($spinfo[$record['csp']]['size']=='S'){
								if ($record['viability'] != 'NA' ){
								$checknote='活性應為 NA。';		
								}
								if ($record['seeds'] != 'NA' ){
								$checknote='種子數應為 NA。';		
								}							
							} else {
								// $checknote='種類名稱不在名錄中，請通知管理員';	}				
						
							} 
						}
					// f fragments = ''
						if ($record['fragments'] != ''){
							$checknote='碎片數應為 空白。';
						}	
					//g sex l!= ''	
						if ($record['sex'] != ''){
							$checknote='性別欄位應為 空白。';
						}
					}	
				} else if ($record['code']=='3') {
				// 3 code = 3
				// a seeds != 0	
					if ($record['seeds']==''){
						$record['seeds']='0';
					}
					if ($record['seeds']!='0'){
						$checknote='種子數應為 0。';						
					} 
				// b viability != ''		
					if ($record['viability']!=''){
						$checknote='活性應為 空白。';						
					} 
				// f fragments = 0
					if ($record['fragments'] == ''){
						$checknote='碎片數不得為空白。';
					}	else if ($record['fragments'] > $record['count']){
					// 	f fragments > count
						$checknote='碎片數不應大於數量。';
					}
				//g sex l!= ''	
					if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
					}	
				} else if ($record['code']=='4') {
				// 3 code = 4
				// a seeds != 0	
					if ($record['seeds']==''){
						$record['seeds']='0';
					}
					if ($record['seeds']!='0'){
						$checknote='種子數應為 0。';						
					} 
				// b viability != ''		
					if ($record['viability']!=''){
						$checknote='活性應為 空白。';						
					} 
				// c count != '1'		
					if ($record['count']!='1'){
						$checknote='數量應為 1。';						
					} 	
				// f fragments = ''
					if ($record['fragments'] != ''){
						$checknote='碎片數應為 空白。';
					}	
				//g sex l!= ''	
					if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
					}	
				} else if ($record['code']=='5') {
				// 3 code = 5
				// a seeds != 0		
					if ($record['seeds']==''){
						$record['seeds']='0';
					}
					if ($record['seeds']!='0'){
						$checknote='種子數應為 0。';						
					} 
				// b viability != ''		
					if ($record['viability']!=''){
						$checknote='活性應為 空白。';						
					} 	
				// f fragments = ''
					if ($record['fragments'] != ''){
						$checknote='碎片數應為 空白。';
					}	
				//g sex l!= ''	
					if ($record['sex'] != ''){
						$checknote='性別欄位應為 空白。';
					}	
				} else if ($record['code']=='6') {
				// 3 code = 6
				// a seeds != 0	
					if ($record['seeds']==''){
						$record['seeds']='0';
					}
					if ($record['seeds']!='0'){
						$checknote='種子數應為 0。';						
					} 
				// b viability != ''		
					if ($record['viability']!=''){
						$checknote='活性應為 空白。';						
					} 	
				// c count != '1'		
					if ($record['count']!='1'){
						$checknote='數量應為 1。';						
					} 
					// f fragments = ''
					if ($record['fragments'] != ''){
						$checknote='碎片數應為 空白。';
					}	
				//d csp = ‘長葉木薑子’	
					if ($record['csp'] != '長葉木薑子'){
						if ($record['sex'] != ''){
						$checknote='性別欄位不得為 空白。';
						}
					} else {
						if ($record['sex'] == ''){
						$checknote='性別欄位應為 空白。';
						}
					}
				} else {
				// 3 code != 1-6	
					$checknote='類別欄位應為 1 - 6 。';
				}
			}
		}

	// 特殊檢查	
	//  csp = 烏+長
			if ($record['csp'] == '栲屬'){
				if ($record['code'] != '6'){
					$checknote='類別欄位應為 6。';
				}
			}
	// trap+種類+類別 不能一樣	
			$checksign=$record['trap'].$record['csp'].$record['code'];
			$checkarray=[];
			if ($type=='n'){
				$dataexit=FsSeedsRecord1::query()->get()->toArray();
			} else {
				$dataexit=FsSeedsRecord1::where('id', 'not like', $record['id'])->get()->toArray();
			}

			if (count($dataexit)>0){
				foreach ($dataexit as $data){
					$checkarray[]=$data['trap'].$data['csp'].$data['code'];
				}

				if (in_array($checksign, $checkarray)){
					$checknote='重複。';
				} 
			}

			// if ($checknote ==''){$checknote='確 。';}

		return $checknote;

	}
}


