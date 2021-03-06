<?php
class Notas {
	
	private $dom;
	private $html;
	private $notas;
	
	public function __construct($html) {
		
		$this->html = $html;
		
		$this->dom = new DOMDocument();
		@$this->dom->loadHTML($this->html);
		
		$this->parse();
	}	
	
	public function toJson() {
		return json_encode($this->notas);
	}
	
		
	private function parse() {
		$PRIMEIRA_TABELA_MATERIA = 3;
		$QNT_RETIRAR_TABELAS_FINAIS = 2;
		
		for($i = $PRIMEIRA_TABELA_MATERIA; $i < $this->dom->getElementsByTagName('table')->length - $QNT_RETIRAR_TABELAS_FINAIS; $i++) {
			$this->notas[] = array(
				'table' => $this->dom->getElementsByTagName('table')->item($i)
			);
		};
		$this->parseMaterias();
		//var_dump($this->notas);
	}
	
	private function parseMaterias() {
		
		$INDEX_NOME = [0, 0];
		$INDEX_ALTERACAO = [1, 0];
		$INDEX_CALCULO = [1, 1];
		$INDEX_TABLE_TITULO = 3;
		$INDEX_TABLE_VALOR = 4;
		
		foreach($this->notas as $index => $materia) {
			$element = $materia['table']->getElementsByTagName('tr');
			$this->notas[$index] = array(
				'nome' => nTrim($element->item($INDEX_NOME[0])->getElementsByTagName('td')->item($INDEX_NOME[1])->nodeValue),
				'alteracao' => $this->alteracao( $element->item($INDEX_ALTERACAO[0])->getElementsByTagName('td')->item($INDEX_ALTERACAO[1])->nodeValue ),
				'calculo' => nTrim(explode(':', $element->item($INDEX_CALCULO[0])->getElementsByTagName('td')->item($INDEX_CALCULO[1])->nodeValue)[1])
			);
			$this->parseNotas( $this->notas[$index], $element->item($INDEX_TABLE_TITULO), $element->item($INDEX_TABLE_VALOR)); 
			$this->parseFaltas( $this->notas[$index], $element->item($INDEX_TABLE_TITULO), $element->item($INDEX_TABLE_VALOR)); 
		}
		//var_dump($this->notas);
	}
	
	private function parseNotas(&$materia, $titulos, $valores) {
		
		$MAX_NOTAS_PARCIAIS = 7;
		$NODEVALUE_EMPTY_LENGTH = 2;
		
		$INDEX_NOTA_TOTAL = 7;
		$INDEX_EXAME_FINAL = 8;
		$INDEX_NOTA_FINAL = 9;
		$INDEX_CONCEITO = 12;
		
		$materia['nota']['total'] = $valores->getElementsByTagName('td')->item($INDEX_NOTA_TOTAL)->nodeValue;
		$materia['nota']['exame'] = $valores->getElementsByTagName('td')->item($INDEX_EXAME_FINAL)->nodeValue;
		$materia['nota']['final'] = $valores->getElementsByTagName('td')->item($INDEX_NOTA_FINAL)->nodeValue;
		$materia['nota']['conceito'] = $valores->getElementsByTagName('td')->item($INDEX_CONCEITO)->nodeValue;
		
		for($i = 0; $i < $MAX_NOTAS_PARCIAIS; $i++) {
			$titulo = $titulos->getElementsByTagName('td')->item($i);
			$valor = $valores->getElementsByTagName('td')->item($i);
			
			if(strlen($titulo->nodeValue) == $NODEVALUE_EMPTY_LENGTH) continue;
		
			$materia['nota']['notas'][] = array(
				'nome' => trim(explode(':', $titulo->nodeValue)[0]),
				'max' => $titulo->getElementsByTagName('b')->item(0)->nodeValue,
				'valor' => $valor->nodeValue
			);
		}
		//var_dump($materia['notas']);
	}
	
	private function parseFaltas(&$materia, $titulos, $valores) {
		
		$INDEX_FALTAS_TEORICAS = 10;
		$INDEX_FALTAS_PRATICAS = 11;
		
		$materia['faltas'] = array(
			'teoricas' => $valores->getElementsByTagName('td')->item($INDEX_FALTAS_TEORICAS)->nodeValue,
			'praticas' => $valores->getElementsByTagName('td')->item($INDEX_FALTAS_PRATICAS)->nodeValue,
		);
		//var_dump($materia['faltas']);
	}
	
	
	
	private function alteracao($valor) {
		$v = explode(':', $valor);
		return "{$v[1]}:{$v[2]}";
	}
}

function nTrim($str) {
	return trim($str, chr(0xC2).chr(0xA0));
}
?> 