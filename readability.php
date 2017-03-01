<?php
include_once(__DIR__ . '/pattern.php');

class Readability
{
	private $patterns;

	function __construct()
	{
		$this->patterns = new Pattern();
	}

	function sentense_count($sample)
	{
		return preg_match_all("/([^\.\!\:\;]+)/", $sample, $sentense_array);
	}

	function syllables_count($sentense)
	{
		$count = 0;
		$words = $this->get_words($sentense);
		foreach ($words as $key => $word) {
			$check = true;
			
			// For words having this pattern must count as 1
			foreach($this->patterns->subtract_syllable_patterns as $pattern){
				if(preg_match("/{$pattern}/", $word) == 1){
					$count--;
					$check = true;
				}
			}

			// For words having this pattern must count as 2
			if($check){
				foreach($this->patterns->add_syllable_patterns as $pattern){
					if(preg_match("/{$pattern}/", $word) == 1){
						$count = $count + 2;
						$check = true;
					}
				}
			}
			
			// For words having this pattern must count as 1
			if($check){
				foreach($this->patterns->prefix_and_suffix_patterns as $pattern){
					if(preg_match("/{$pattern}/", $word) == 1){
						$count--;
						$check = true;
					}
				}
			}

			// For words having this pattern must count as 1
			if($check){
				foreach($this->patterns->problem_words as $pattern => $patternCount){
					if(preg_match("/{$pattern}/", $word) == 1){
						$count += $patternCount;
						$check = true;
					}
				}
			}

			if($check){
				foreach(['a', 'e', 'i', 'o', 'u'] as $vowel){
					$count += substr_count($word, $vowel);
				}
			}
		}
		return $count;
	}

	function get_words($senstense){
		return array_map('trim', explode(' ', $senstense), ['\.\,\?\:\;']);
	}

	private function calculate_score($sample)
	{
		$totalWords = count($this->get_words($sample));
		$totalSentenes = $this->sentense_count($sample);
		$totalSyllables = $this->syllables_count($sample);

		$ASL = ($totalSentenes == 0) ? 0 : ($totalWords / $totalSentenes);
		$ASW = ($totalWords == 0) ? 0 : ($totalSyllables / $totalWords);

		return round(206.835 - (1.015 * $ASL) - (84.6 * $ASW), 2);
	}

    function ease_score($writing_sample)
    {
        # Calculate score
        $score = $this->calculate_score($writing_sample);
        
        # Return of 0.0 to 100.0
        return $score;
    }
}

?>
