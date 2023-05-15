<?php

if (!function_exists('formatValidate')) {
    function formatValidate($errors, $validateLaravel = true){
        $data = [];
        if($validateLaravel) {
            $errors = $errors->toArray();
            foreach ($errors as $error) {
                foreach ($error as $value) {
                    $data[] = '- ' . $value;
                }
            }
        }
        else{
            if(is_string($errors)){
                $data[] = $errors;
            }
            else {
                foreach ($errors as $value) {
                    $data[] = '- ' . $value;
                }
            }
        }
        return implode(PHP_EOL, $data);
    }
}

if (!function_exists('formatOnlyNumber')) {
    function formatOnlyNumber($cpf_cnpj){
        return trim(preg_replace('/[^0-9]/', '', $cpf_cnpj));
    }
}

if (!function_exists('validateCpfCnpj')) {
    function validateCpfCnpj($cpf_cnpj) {
        if (preg_match('/^(\d)\1*$/', $cpf_cnpj)) {
            return false; // número repetido
        }

        if (strlen($cpf_cnpj) == 11) { // CPF
            // Calcula o primeiro dígito verificador
            $soma = 0;
            for ($i = 0; $i < 9; $i++) {
                $soma += intval($cpf_cnpj[$i]) * (10 - $i);
            }
            $digito_verificador_1 = ($soma % 11 < 2) ? 0 : (11 - $soma % 11);

            // Calcula o segundo dígito verificador
            $soma = 0;
            for ($i = 0; $i < 10; $i++) {
                $soma += intval($cpf_cnpj[$i]) * (11 - $i);
            }
            $digito_verificador_2 = ($soma % 11 < 2) ? 0 : (11 - $soma % 11);

            // Verifica se os dígitos verificadores estão corretos
            return ($cpf_cnpj[9] == $digito_verificador_1 && $cpf_cnpj[10] == $digito_verificador_2);
        } else if (strlen($cpf_cnpj) == 14) { // CNPJ
            // Calcula o primeiro dígito verificador
            $soma = 0;
            $peso = 5;
            for ($i = 0; $i < 12; $i++) {
                $soma += intval($cpf_cnpj[$i]) * $peso;
                $peso = ($peso == 2) ? 9 : ($peso - 1);
            }
            $digito_verificador_1 = ($soma % 11 < 2) ? 0 : (11 - $soma % 11);

            // Calcula o segundo dígito verificador
            $soma = 0;
            $peso = 6;
            for ($i = 0; $i < 13; $i++) {
                $soma += intval($cpf_cnpj[$i]) * $peso;
                $peso = ($peso == 2) ? 9 : ($peso - 1);
            }
            $digito_verificador_2 = ($soma % 11 < 2) ? 0 : (11 - $soma % 11);

            // Verifica se os dígitos verificadores estão corretos
            return ($cpf_cnpj[12] == $digito_verificador_1 && $cpf_cnpj[13] == $digito_verificador_2);
        } else {
            return false; // o número não é nem um CPF nem um CNPJ válido
        }
    }
}
