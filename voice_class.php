<?php

class google_voice_api_class
{
	var $url = "http://translate.google.com/translate_tts";
	var $cript_key = 'HISJSKMJUJSNWHWNSK';
	var $params = array();
	var $path_name;
	var $file_name;
	var $file_content;
	var $filetype;
	
	//CONTRUTOR DA CLASSE
	function google_voice_api_class($content) {	
		$this->params['ie'] = "UTF-8";
		$this->params['tl'] = "pt";
		$this->params['url_prev'] = "input";
		$this->filetype = '.mp3';
		$this->pathname = 'audio';
		
		
		$this->file_name = md5($this->cript_key.time()).$this->filetype;
		$this->file_content = $content;
	}	
	
	//RETORNA UM ARQUIVO MP3 COM TODO O CONTEUDO COMPILADO
	function get_voice() {
		set_time_limit(300);
		$content = '';
		$frase = $this->split_content();
		foreach ($frase as $f)
		{
			if ($f)
				$this->file_content .= file_get_contents($this->parse_url() . '&q=' . trim(urlencode($f)));
		}	
		$this->file_content = $this->getJingle().$this->file_content.$this->getJingle();
		return $this->create_file();
	}
	
	function get_sample_text() {
		$text_sample_name = 'sampleText.txt';
		if (@$sample_content = utf8_encode(file_get_contents($text_sample_name)))
			return $sample_content;
		else
			return '';
	}	
	
	//ADICIONA O JINGLE AO TRECHO DO CONTEUDO.
	function getJingle($position = 0){
		/**
		POSITION 0 - INÍCIO DO TRECHO
		POSITION 1 - FINAL DO TRECHO
		**/

		$jinglePath = 'jingles';
		$jingleName = 'jingleSample.mp3';
		
		$str_spacer = "... , ...";
		if ($position)
			return $str_spacer.file_get_contents($jinglePath.'/'.$jingleName);
		else
			return file_get_contents($jinglePath.'/'.$jingleName).$str_spacer;		
		
	}
	
	//RECUPERA O NOME DO ARQUIVO CRIADO
	function get_file_name() {
		return $this->pathname.'/'.$this->file_name ? $this->pathname.'/'.$this->file_name : null;
	}
	
	//QUEBRA O CONTEUDO EM PEQUENAS PARTES COM TAMANHO MENOS QUE 100 CARACTERES
	function split_content() {
		
		//return explode('\n', wordwrap($this->file_content, 100, '\n', false));
		//var_dump(explode('\n', wordwrap($this->file_content, 100, '\n', false)));
		
		/**
		REGRAS DE PRIORIDADE PARA QUEBRAR A FRASE E PARTES
		 1)  PONTO FINAL
		 2) PONTO-VÍRGULA
		 3) VÍRGULA
		 4) ORAÇÃO COM MAIS DE 100 CARACTERES
		 **/
		 
		$parts = array();
		$content = $this->clean_content();
		
		//REGRA 1 - PONTO FINAL
		if (strlen($content) > 100)
			$parts = explode('. ', $content);
		
			#colocando os pontos-finais de volta no final da frase
			foreach($parts as $key => $value)
			{
				if ( !in_array (substr($value, strlen($value)-1, strlen($value)), array(';')) )
					$parts[$key] = $value.'. ';
			}	
		
		//REGRA 2 -  PONTO-VÍRGULA
		foreach($parts as $key => $part)
			if (strlen($part) > 100)
				$parts[$key] = explode('; ', $part);
		
		$parts = $this->linear_array($parts);
		
		
		//REGRA 3 -  VÍRGULA
		foreach($parts as $key => $part)
			if (strlen($part) > 100)
				$parts[$key] = explode(', ', $part);
		
		$parts = $this->linear_array($parts);
		
			#colocando vírgulas de volta no final de oração 
			foreach($parts as $key => $value)
			{
				if ( !in_array (substr($value, strlen($value)-2, strlen($value)), array('. ','; ')) )
					$parts[$key] = $value.', ';		
			}		
		
		//REGRA 4 - ORAÇÃO COM MAIS DE 100 CARACTERES
		foreach($parts as $key => $part)
		{
			if (strlen($part) > 100)
				$parts[$key] = explode('\n', wordwrap($part, 100, '\n', false));
		}
		
		$parts = $this->linear_array($parts);
		//var_dump($parts); exit;
		return $parts;
	
	}
	
	//LIMPA E FAZ TRATAMENTO DE TEXTO
	function clean_content() {
		$string_cleaned = $this->file_content;
		
		//TRATAMENTO SUPERFICIAL
		$trashes = array('"','\\');
		$cleany = array(' ');
		$string_cleaned = str_replace($trashes, $cleany, $string_cleaned);
		
		//TRATAMENTO ESPECÍFICO
		$matches = array();
		$patterns = array(
				"/[0-9]\,[0-9]/" => array(',' => ' virgula '),
				"/\;/" => array(';' => '.'),
				"/\-/" => array('-' => ' '),
				"/[\[\]]/" => array('[' => '.'),
				"/[\[\]]/" => array(']' => '.'),
		);
		
		foreach($patterns as $pattern => $array_str)
		{
			if (preg_match_all($pattern, $this->file_content, $matches))
			{
				foreach($matches as $matched)
				{
					foreach ($array_str as $old_string => $new_string)
						$clean = str_replace($old_string, $new_string, $matched);
				}		
				$string_cleaned = str_replace($matched, $clean, $string_cleaned);
			}
			
		}
		
		return $string_cleaned;
	}
	
	//MONTA A URL DE CHAMADA PARA O SERVIÇO
	function parse_url() {
		$url = 	$this->url.'?';
		foreach($this->params as $param => $value)
			$url .= '&'.$param.'='.$value;

		return $url;		
	}
	
	//HELPER PARA LINEARIZAR O ARRAY DE 2 DIMENSOES E 1 DIMENSAO
	function linear_array($array_2)
	{
		$array_temp = array();
		if(is_array($array_2))
		{
			foreach($array_2 as $arr)
			{
				if (is_array($arr))
					foreach($arr as $a)
						array_push($array_temp, $a);
				else
					array_push($array_temp, $arr);
			}
		}
		
		return $array_temp;
	}
	
	//CRIA O ARQUIVO MP3
	function create_file()
	{
		if (!is_dir($this->pathname))
			$dir = mkdir($this->pathname, '777');
		else
			$dir = true;
		
		if($dir)
		{
			$fp = fopen($this->pathname.'/'.$this->file_name, "w");
			$writeFile = fwrite($fp, $this->file_content);
			fclose($fp);
		}
		else
			die('Permissão negada para criar diretório de áudio temporário.');
		
		return $writeFile ? true : false;		
	}

}	
	
?>