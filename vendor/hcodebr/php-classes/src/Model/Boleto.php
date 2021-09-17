<?php
namespace Hcode\Model;

// NÃO ALTERAR!
//include("include/funcoes_itau.php"); 
//include("include/layout_itau.php");

class Boleto 
{
    // DADOS DO BOLETO PARA O SEU CLIENTE
	private $dias_de_prazo_para_pagamento;
	private $taxa_boleto;
	private $data_venc ;  // Prazo de X dias OU informe data: "13/04/2006"; 
	private $valor_cobrado; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	private $valor_boleto;
	private $dadosboleto = [];

	public function __construct($dias_de_prazo_para_pagamento,$valor_cobrado, $dadosboleto = array(),$taxa_boleto =5)
	{
		$this->dias_de_prazo_para_pagamento = $dias_de_prazo_para_pagamento;
		$this->taxa_boleto = $taxa_boleto;
		$this->data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
		
		$this->valor_cobrado = $valor_cobrado; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
		$this->valor_cobrado = str_replace(",", ".",$valor_cobrado);
		$this->valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

		$this->dadosboleto = [
		
			"nosso_numero" => $dadosboleto["nosso_numero"],  // Nosso numero - REGRA: Máximo de 8 caracteres!
			"numero_documento" => $dadosboleto["numero_documento"],	// Num do pedido ou nosso numero
			"data_vencimento" => $this->data_venc, // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
			"data_documento" => date("d/m/Y"), // Data de emissão do Boleto
			"data_processamento" => date("d/m/Y"), // Data de processamento do boleto (opcional)
			"valor_boleto" => $this->valor_boleto, 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
			
			// DADOS DO SEU CLIENTE
			"sacado"   => $dadosboleto["sacado"],
			"endereco1"   => $dadosboleto["endereco1"],
			"endereco2"   => $dadosboleto["endereco2"],
		
			// INFORMACOES PARA O CLIENTE
			 "demonstrativo1"   => "Pagamento de Compra na Loja Andre Ecommerce",
			 "demonstrativo2"   => "Taxa bancária - R$ 10,00",
			 "demonstrativo3"   => "",
			 "instrucoes1"   => "- Sr. Caixa, cobrar multa de 2% após o vencimento",
			 "instrucoes2"   => "- Receber até 10 dias após o vencimento",
			 "instrucoes3"   => "- Em caso de dúvidas entre em contato conosco: andreluis2608@gmai.com",
			 "instrucoes4"   => "- Emitido pelo sistema Projeto Loja Andrecommerce - www.andrecommerce.com.br",
		
			// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
			 "quantidade"   => 1,
			 "valor_unitario"   =>  $this->valor_boleto,
			 "aceite"   => "",		
			 "especie"   => "R$",
			 "especie_doc"   => "",
		
		
			// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
		
		
			// DADOS DA SUA CONTA - ITAÚ
			 "agencia"   => "1690", // Num da agencia, sem digito
			 "conta"   => "48781",	// Num da conta, sem digito
			 "conta_dv"   => "2", 	// Digito do Num da conta
		
			// DADOS PERSONALIZADOS - ITAÚ
			 "carteira"   => "175",  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157
		
			// SEUS DADOS
			 "identificacao"   => "Hcode Treinamentos",
			 "cpf_cnpj"   => "24.700.731/0001-08",
			 "endereco"   => "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120",
			 "cidade_uf"   => "São Bernardo do Campo - SP",
			 "cedente"   => "HCODE TREINAMENTOS LTDA - ME",
		
			];
	}

	public function getDadoBoleto()
	{
		return $this->dadosboleto;
	}
}