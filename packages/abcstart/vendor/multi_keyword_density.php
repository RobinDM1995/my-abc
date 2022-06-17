<?php
/*
 ======================================================================
 KeywordDensityChecker v1.1.1

 Simple yet powerfull PHP class to get the keyword density of
 a website.

 by Stephan Schmitz, info@eyecatch-up.de

 Latest version, features, manual and examples:
     http://code.eyecatch-up.de/?p=155
 ----------------------------------------------------------------------
 LICENSE

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License (GPL)
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 To read the license please visit http://www.gnu.org/copyleft/gpl.html
 ======================================================================
*/

/**
* KeywordDensityChecker
* Simple yet powerfull PHP class to get the keyword density of
* a website.
*/
class MultiKeywordDensity {
// -------------------------------------------------------------------
// @params
// -------------------------------------------------------------------
    var $domain;              // Domain to check
// -------------------------------------------------------------------
// PRIVATE FUNCTIONS
// -------------------------------------------------------------------
    // -------------------------------------------------------------------
    // Private Function cURL
    // -------------------------------------------------------------------
    private function cURL(){
      // -------------------------------------------------------------------
      // Save result page to string using curl
      // -------------------------------------------------------------------
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL,$this->domain);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
      $str = curl_exec($ch);
      return $str;
    } // End of private function cURL
    // -------------------------------------------------------------------
    // Private Function to return result page as string
    // -------------------------------------------------------------------
    private function plainText(){
      // -------------------------------------------------------------------
      // External classes
      // -------------------------------------------------------------------
      require_once('ext/class.html2text.inc');
      //require_once('ext/Html2Text.php');
      // -------------------------------------------------------------------
      // Save google result page to string using curl
      // -------------------------------------------------------------------
      $str = $this->cURL();
      // -------------------------------------------------------------------
      // Extract the plain text
      // -------------------------------------------------------------------

      $extraction = new html2text($str, true);
      $extraction->set_base_url($this->domain);
      // -------------------------------------------------------------------
      // Return string
      // -------------------------------------------------------------------

      //return strtolower($extraction->get_text());
      return preg_replace('/[^a-zA-Z0-9]+/i', ' ', strtolower($extraction->get_text()));

    } // End of private function plainText
    // -------------------------------------------------------------------
    // Private Function to clean out the plain text
    // -------------------------------------------------------------------
    private function trim_replace($string) {
      $string = trim($string);
      return (string)str_replace(array("\r", "\r\n", "\n"), '', $string);
    }
    // -------------------------------------------------------------------
    // Private Function to calculate the keyword density from plain text
    // -------------------------------------------------------------------
    private function calcDensity(){
      // -------------------------------------------------------------------
      // Prepare string
      // -------------------------------------------------------------------
      $words = explode(" ",$this->plainText());

      $common_words = "i,he,she,it,and,me,my,you,the,voor,de,het,een,als,mijn,over,deze,onze,naar,le,la,du,un,une,moi,toi,votre,notre";
      $common_words = strtolower($common_words);
      $common_words = explode(",", $common_words);
      // -------------------------------------------------------------------
      // Get keywords
      // -------------------------------------------------------------------
      $words_sum = 0;
      foreach ($words as $value){
        $common = false;
        $value = $this->trim_replace($value);
        if (strlen($value) > 3){
          foreach ($common_words as $common_word){
            if ($common_word == $value){
              $common = true;
            }
          }
          if ($common != true){
            if (!preg_match("/http/i", $value) && !preg_match("/mailto:/i", $value)) {
              $keywords[] = $value;
              $words_sum++;
            }
          }
        }
      }
      // -------------------------------------------------------------------
      // Do some maths and write array
      // -------------------------------------------------------------------
      $keywords = array_count_values($keywords);
      arsort($keywords);
      $results = array();
                  $results []= array(
                          'total words' => $words_sum
      );
      foreach ($keywords as $key => $value){
            $percent = 100 / $words_sum * $value;
                        $results []= array(
                    'keyword' => trim($key),
                                'count' => $value,
                                'percent' => round($percent, 2)
            );
      }
      // -------------------------------------------------------------------
      // Return array
      // -------------------------------------------------------------------
      return $results;
    } // End of private function calcDensity


    //multi density
    private function calcMultiDensity(){
      // -------------------------------------------------------------------
      // Prepare string
      // -------------------------------------------------------------------
      $wordString = $this->plainText();
      $wordString = preg_replace(array('/\b\w{1,3}\b/','/\s+/'),array('',' '),$wordString);

      //print $wordString;
      $filterwordsArray = array(" a ", " about ", " above ", " above ", " across ","i "," he "," she "," it "," and "," me "," my "," you "," the "," voor "," de "," het "," een "," als "," mijn "," over "," deze "," onze "," naar "," le "," la "," du "," un "," une "," moi "," toi "," votre "," notre"," wat ");

    	// Remove filter words form input and count the number of removed words
    	$wordStringFiltered = str_replace($filterwordsArray, ' ',  $wordString, $replaceCount);

    	// Count the number of words found within the filtered words string (input), returns an array
    	$keywordsArray = str_word_count($wordString, 1);

    	// Count the number of words found within the filtered words string (input), returns a string
    	$wordCount = str_word_count($wordString, 0);

      $keywordsSortedArray = $this->keywordSorting($keywordsArray, $wordCount);

      return $keywordsSortedArray;
    } // End of private function calcDensity

    private function keywordSorting($keywordsArray, $wordCount){

		$keywordsSorted0 = ''; // 1 word match
		$keywordsSorted1 = ''; // 2 word phrase match
		$keywordsSorted2 = ''; // 3 word phrase match
		$keywordsSorted3 = ''; // 4 word phrase match

		for ($i = 0; $i < count($keywordsArray); $i++){
			// 1 word phrase match
			if ($i+0 < $wordCount){
				$keywordsSorted0 .= $keywordsArray[$i].',';
			}
			// 2 word phrase match
			if ($i+1 < $wordCount){
				$keywordsSorted1 .= $keywordsArray[$i].' '.$keywordsArray[$i+1].',';
			}
			// 3 word phrase match
			if ($i+2 < $wordCount){
				$keywordsSorted2 .= $keywordsArray[$i].' '.$keywordsArray[$i+1].' '.$keywordsArray[$i+2].',';
			}
			// 4 word phrase match
			if ($i+3 < $wordCount){
				$keywordsSorted3 .= $keywordsArray[$i].' '.$keywordsArray[$i+1].' '.$keywordsArray[$i+2].' '.$keywordsArray[$i+3].',';
			}
		}

		for ($i = 0; $i <= 3; $i++){

			// Build array form string.
			${'keywordsSorted'.$i} = array_filter(explode(',', ${'keywordsSorted'.$i}));
			${'keywordsSorted'.$i} = array_count_values(${'keywordsSorted'.$i});
			asort(${'keywordsSorted'.$i});
			arsort(${'keywordsSorted'.$i});

			foreach (${'keywordsSorted'.$i} as $key => $value){
				${'keywordsSorted'.$i}[$key] = array($value, number_format((100 / $wordCount * $value),2));
			}
		}

		// return array
		return array($keywordsSorted0, $keywordsSorted1, $keywordsSorted2, $keywordsSorted3);
	}
// -------------------------------------------------------------------
// PUBLIC FUNCTION
// -------------------------------------------------------------------
    // -------------------------------------------------------------------
    // Public Function to return the keyword density result array
    // -------------------------------------------------------------------
    public function result(){
      return $this->calcDensity();
    } // End of function KD

    public function multiresult(){
      return $this->calcMultiDensity();
    } // End of function KD
} // End of class KeywordDensityChecker v1.1.1 by Stephan Schmitz @ http://code.eyecatch-up.de/?p=155
?>
