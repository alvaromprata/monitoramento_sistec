<?php
$servername = "192.168.0.49";
$username = "thiagoantonio";
$password = "thi15ago87";
$dbnome = "monitoramento_sistec";

//$servername = "localhost";
//$username = "root";
//$password = "";
//$dbnome = "monitoramento_sistec";
//=======================> Tabelas
$tabelaSistec = "aluno_sistec";
$tabelaSistecSemCpf = "aluno_sistec_sem_cpf";
$tabelaAcad = "aluno_acad";
$tabelaAcadSemCpf = "aluno_acad_sem_cpf";
$tabelaSitDivergente = "sit_divergente";
$tabelaVariosCpf = "varios_cpf";
$tabelaMesmoCpf = "mesmo_cpf";
$tabelaNaoExisteSistec = "nao_existe_sistec";
$tabelaNaoExisteAcad = "nao_existe_acad";
$tabelaNomeDiferenteMesmoCpf = "nome_diferentes_mesmo_cpf";
$tabelaCursoSistec = "curso_sistec";

$conn = new mysqli($servername, $username, $password, $dbnome);

if ($conn->connect_error) {
    die("Não foi possível conectar!: " . $conn->connect_error);
} else {
    echo "* Conectado * </br></br></br>";
}

//=======================> Aguarda o click no botão submit;

if (isset($_POST['submit_curso_sistec'])) {
    limparTabela($conn, $tabelaCursoSistec);
    cargaDosDadosAcad($conn, $tabelaCursoSistec);
}
if (isset($_POST['purge_curso_sistec'])) {
    limparTabela($conn, $tabelaCursoSistec);
    limparTabela($conn, $tabelaCursoSistec);
    limparIDs($conn, $tabelaCursoSistec);
}
if (isset($_POST['submit_acad'])) {
    limparTabela($conn, $tabelaAcad);
    cargaDosDadosAcad($conn, $tabelaAcad);
    //removerAcentos($conn, $tabelaAcad);
}
if (isset($_POST['purge_acad'])) {
    limparTabela($conn, $tabelaAcad);
    limparTabela($conn, $tabelaAcadSemCpf);
    limparIDs($conn, $tabelaAcad);
}
if (isset($_POST['sem_cpf_acad'])) {
    limparTabela($conn, $tabelaAcadSemCpf);
    buscarAlunosAcadSemCPF($conn, $tabelaAcad, $tabelaAcadSemCpf);
}
if (isset($_POST['submit_sistec'])) {
    //limparTabela($conn, $tabelaSistec);
    //limparIDs($conn, $tabelaSistec);
    cargaDosDadosSistec($conn, $tabelaSistec);
}
if (isset($_POST['purge_sistec'])) {
    limparTabela($conn, $tabelaSistec);
    limparTabela($conn, $tabelaSistecSemCpf);
    limparIDs($conn, $tabelaSistec);
}
if (isset($_POST['sem_cpf_sistec'])) {
    limparTabela($conn, $tabelaSistecSemCpf);
    buscarAlunosSistecSemCPF($conn, $tabelaSistec, $tabelaSistecSemCpf);
}
if (isset($_POST['sit_periodo'])) {
    limparTabela($conn, $tabelaSitDivergente);
    comparaSituacao($tabelaSistec, $tabelaAcad, $tabelaSitDivergente, $conn);
}
if (isset($_POST['purge_sit_periodo'])) {
    limparTabela($conn, $tabelaSitDivergente);
}
if (isset($_POST['cpf_divergente'])) {
    limparTabela($conn, $tabelaVariosCpf);
    cpfDivergente($conn, $tabelaAcad, $tabelaSistec, $tabelaVariosCpf);
}
if (isset($_POST['purge_cpf_divergente'])) {
    limparTabela($conn, $tabelaVariosCpf);
}
if (isset($_POST['nome_diferentes'])) {
    nomeDiferentesMesmoCpf($conn, $tabelaAcad, $tabelaSistec, $tabelaNomeDiferenteMesmoCpf);
}
if (isset($_POST['purge_mesmo_cpf'])) {
    limparTabela($conn, $tabelaMesmoCpf);
}
if (isset($_POST['nao_existe_sistec'])) {
    naoExisteSistec($conn, $tabelaAcad, $tabelaSistec, $tabelaNaoExisteSistec);
}
if (isset($_POST['purge_nao_existe_sistec'])) {
    limparTabela($conn, $tabelaNaoExisteSistec);
}
if (isset($_POST['nao_existe_acad'])) {
    limparTabela($conn, $tabelaNaoExisteAcad);
    naoExisteAcad($conn, $tabelaAcad, $tabelaSistec, $tabelaNaoExisteAcad);
}
if (isset($_POST['purge_nao_existe_acad'])) {
    limparTabela($conn, $tabelaNaoExisteAcad);
}
if (isset($_POST['exibir_acad'])) {
    imprimir($conn, $tabelaAcad);
}

$conn->close();

function cargaDosDadosAcad($conn, $tabela) {
    $cpfCompleto = "";
    $fname = $_FILES['sel_file']['name'];
    $chk_ext = explode(".", $fname);
    $inseridos = 0;

    if (strtolower(end($chk_ext)) == "csv") {
        $filename = $_FILES['sel_file']['tmp_name'];
        $csv = fopen($filename, "r");
        //Capturo a primeira linha e não faço nada com ela, após isso inicio o laço que vai inserir os dados no banco
        fgetcsv($csv, 1000, ";");

        while (($valores = fgetcsv($csv, 1000, ";")) !== FALSE) {
            //Deixa apenas números na string
            $soNum = soNumeros($valores[6]);

            //Completa a string com zero a esquerda           
            if (strlen($soNum) < 11) {
                $cpfCompleto = completarCpf($soNum);
            }

            //Remove acentos, apostrofe, espaços e deixa tudo maiusculo               
            $nomeTratado = tratarNomes($valores[5]);

            if ($cpfCompleto != "") {
                $sql = "INSERT INTO $tabela(cod_instituicao,cod_curso_acad,sigla_curso,curso,num_matricula,nome,cpf,data_nascimento,sexo,nome_mae,ano_letivo_ini,periodo_letivo_ini,sit_matricula,desc_sit_matricula,data_conclusao)
                        VALUES ('$valores[0]','$valores[1]','$valores[2]','$valores[3]','$valores[4]','$nomeTratado','$cpfCompleto','$valores[7]','$valores[8]','$valores[9]','$valores[10]','$valores[11]','$valores[12]','$valores[13]','$valores[14]')";
            } else {
                $sql = "INSERT INTO $tabela(cod_instituicao,cod_curso_acad,sigla_curso,curso,num_matricula,nome,cpf,data_nascimento,sexo,nome_mae,ano_letivo_ini,periodo_letivo_ini,sit_matricula,desc_sit_matricula,data_conclusao)
                        VALUES ('$valores[0]','$valores[1]','$valores[2]','$valores[3]','$valores[4]','$nomeTratado','$soNum','$valores[7]','$valores[8]','$valores[9]','$valores[10]','$valores[11]','$valores[12]','$valores[13]','$valores[14]')";
            }
            $conn->query($sql);
            $cpfCompleto = "";
            $inseridos++;
        }
        fclose($csv);
        echo "Total de Registros ACAD: " . $inseridos + 1;
    }
}

function cargaDosDadosSistec($conn, $tabela) {
    $separador = '-';
    $cpfCompleto = "";
    $cod = 0;
    $fname = $_FILES['sel_file']['name'];
    $chk_ext = explode(".", $fname);
    $inseridos = 0;

    if (strtolower(end($chk_ext)) == "csv") {
        $filename = $_FILES['sel_file']['tmp_name'];
        $csv = fopen($filename, "r");
        fgetcsv($csv, 1000, ";");

        while (($valores = fgetcsv($csv, 1000, ";")) !== FALSE) {
            //Deixa apenas números na string
            $soNum = soNumeros($valores[3]);
            //Completa a string com zero a esquerda           
            if (strlen($soNum) < 11) {
                $cpfCompleto = completarCpf($soNum);
                $soNum = $cpfCompleto;
            }

            //Código da instituição de ensino para verificar se é EAD
            $cod = $valores[19];

            //Trata a informação de ano e período dos ciclos que estiverem seguindo o padrão (ano.periodo)            
            $anoPeriodoTratado = verificaCiclo($valores[15], $separador, $cod);

            //Remove acentos, apostrofe, espaços e deixa tudo maiusculo
            $nomeTratado = tratarNomes($valores[2]);

            if ($cpfCompleto != "") {
                $sql = "INSERT INTO aluno_sistec(co_aluno_identificado,co_aluno,nome,cpf,ds_email,ds_senha,cod_sistec,cod_ciclo_matricula,cod_status_ciclo_matricula,cod_curso,nu_carga_horaria,dt_data_inicio,dt_data_fim_previsto,co_unidade_ensino,co_periodo_cadastrado,no_ciclo_matricula,st_ativo,cod_tipo_oferta_curso,cod_tipo_instituicao,cod_instituicao,co_portifolio,co_tipo_nivel_oferta_curso,co_tipo_programa_curso,st_carga,dt_data_finalizado,nu_vagas_ofertadas,nu_total_inscritos,st_etec,co_polo,uab,st_previsto,nu_vagas_previstas,no_status_matricula,ano_ciclo_inicial,periodo_ciclo_inicial,ano_ciclo_conclusao,periodo_ciclo_conclusao)
                        VALUES ('$valores[0]','$valores[1]','$nomeTratado','$soNum','$valores[4]','$valores[5]','$valores[6]','$valores[7]','$valores[8]','$valores[9]','$valores[10]','$valores[11]','$valores[12]','$valores[13]','$valores[14]','$valores[15]','$valores[16]','$valores[17]','$valores[18]','$valores[19]','$valores[20]','$valores[21]','$valores[22]','$valores[23]','$valores[24]','$valores[25]','$valores[26]','$valores[27]','$valores[28]','$valores[29]','$valores[30]','$valores[31]','$valores[32]','$anoPeriodoTratado[0]','$anoPeriodoTratado[1]','$anoPeriodoTratado[2]','$anoPeriodoTratado[3]')";
            } else {
                $sql = "INSERT INTO aluno_sistec(co_aluno_identificado,co_aluno,nome,cpf,ds_email,ds_senha,cod_sistec,cod_ciclo_matricula,cod_status_ciclo_matricula,cod_curso,nu_carga_horaria,dt_data_inicio,dt_data_fim_previsto,co_unidade_ensino,co_periodo_cadastrado,no_ciclo_matricula,st_ativo,cod_tipo_oferta_curso,cod_tipo_instituicao,cod_instituicao,co_portifolio,co_tipo_nivel_oferta_curso,co_tipo_programa_curso,st_carga,dt_data_finalizado,nu_vagas_ofertadas,nu_total_inscritos,st_etec,co_polo,uab,st_previsto,nu_vagas_previstas,no_status_matricula,ano_ciclo_inicial,periodo_ciclo_inicial,ano_ciclo_conclusao,periodo_ciclo_conclusao)
                        VALUES ('$valores[0]','$valores[1]','$nomeTratado','$soNum','$valores[4]','$valores[5]','$valores[6]','$valores[7]','$valores[8]','$valores[9]','$valores[10]','$valores[11]','$valores[12]','$valores[13]','$valores[14]','$valores[15]','$valores[16]','$valores[17]','$valores[18]','$valores[19]','$valores[20]','$valores[21]','$valores[22]','$valores[23]','$valores[24]','$valores[25]','$valores[26]','$valores[27]','$valores[28]','$valores[29]','$valores[30]','$valores[31]','$valores[32]','$anoPeriodoTratado[0]','$anoPeriodoTratado[1]','$anoPeriodoTratado[2]','$anoPeriodoTratado[3]')";
            }
            $conn->query($sql);
            $cpfCompleto = "";
            $inseridos++;
        }
        fclose($csv);
        echo "Total de Registros SISTEC: " . $inseridos + 1;
    }
}

function comparaSituacao($tabelaSistec, $tabelaAcad, $tabelaSitDivergente, $conn) {
    $sql = "INSERT INTO $tabelaSitDivergente(id_sistec,cod_sistec,num_matricula,curso,nome,cpf,sit_acad,sit_sistec)
            SELECT $tabelaSistec.id, $tabelaSistec.cod_sistec, $tabelaAcad.num_matricula, $tabelaSistec.nome, $tabelaAcad.curso, $tabelaSistec.cpf, $tabelaAcad.desc_sit_matricula, $tabelaSistec.no_status_matricula
            FROM $tabelaSistec
            INNER JOIN $tabelaAcad
            ON $tabelaSistec.no_status_matricula = 'EM_CURSO' && $tabelaAcad.desc_sit_matricula != 'matriculado' && $tabelaAcad.cpf = aluno_sistec.cpf
            ORDER BY aluno_sistec.nome";
    $conn->query($sql);
}

/*
  function comparaSituacao($tabelaSistec, $tabelaAcad, $tabelaSitDivergente) {
  $sql = "INSERT IGNORE INTO $tabelaSitDivergente(id_sistec,cod_sistec,num_matricula,nome,cpf,sit_acad,sit_sistec)
  SELECT $tabelaSistec.id, $tabelaSistec.cod_sistec, $tabelaAcad.num_matricula, $tabelaSistec.nome, $tabelaSistec.cpf, $tabelaAcad.desc_sit_matricula, $tabelaSistec.no_status_matricula
  FROM $tabelaSistec
  INNER JOIN $tabelaAcad
  ON $tabelaSistec.no_status_matricula = 'EM_CURSO' && $tabelaAcad.desc_sit_matricula != 'matriculado' && $tabelaAcad.cpf = aluno_sistec.cpf
  ORDER BY aluno_sistec.nome";
  sc_exec_sql($sql);
  }
 */

function contarRegistros($filename) {
    $fp = file($filename);
    echo count("FP:" . $fp);
}

function buscarAlunosAcadSemCPF($conn, $tabela, $tabelaSitDivergente) {
    $sql = "INSERT INTO $tabelaSitDivergente(id,num_matricula,nome,cpf)
            SELECT $tabela.id, $tabela.num_matricula, $tabela.nome, $tabela.cpf 
            FROM $tabela WHERE cpf IS NULL || length(cpf) < 11 || cpf = ''";
    $conn->query($sql);
}

function buscarAlunosSistecSemCPF($conn, $tabela, $tabelaSitDivergente) {
    $sql = "INSERT INTO $tabelaSitDivergente(id,cod_sistec,nome,cpf)
            SELECT $tabela.id, $tabela.cod_sistec, $tabela.nome, $tabela.cpf 
            FROM $tabela WHERE cpf IS NULL || length(cpf) < 11 || cpf = ''";
    $conn->query($sql);
}

function limparTabela($conn, $tabela) {
    $sql = "DELETE FROM $tabela";
    $conn->query($sql);
}

function limparIDs($conn, $tabela) {
    $sql = "ALTER TABLE $tabela AUTO_INCREMENT = 0";
    $conn->query($sql);
}

function cpfDivergente($conn, $tabelaAcad, $tabelaSistec, $tabelaVariosCpf) {
    $sql = "INSERT INTO $tabelaVariosCpf(nome,cpf_acad,cpf_sistec)
    SELECT $tabelaSistec.nome, $tabelaAcad.cpf, $tabelaSistec.cpf
    FROM $tabelaSistec, $tabelaAcad WHERE $tabelaAcad.cpf != $tabelaSistec.cpf && $tabelaAcad.nome = $tabelaSistec.nome";
    $conn->query($sql);
}

function nomeDiferentesMesmoCpf($conn, $tabelaAcad, $tabelaSistec, $tabelaDestino) {
    $sql = "INSERT INTO $tabelaDestino (nome_acad,nome_sistec,cpf)
    SELECT $tabelaAcad.nome, $tabelaSistec.nome, $tabelaSistec.cpf
    FROM $tabelaAcad, $tabelaSistec WHERE $tabelaAcad.nome != $tabelaSistec.nome && $tabelaAcad.cpf = $tabelaSistec.cpf";
    $conn->query($sql);
}

function naoExisteSistec($conn, $tabelaAcad, $tabelaSistec, $tabelaNaoExisteSistec) {
    $sql = "INSERT INTO $tabelaNaoExisteSistec(nome,cpf,num_matricula,nome_curso)
            SELECT $tabelaAcad.nome, $tabelaAcad.cpf, $tabelaAcad.num_matricula, $tabelaAcad.curso
            FROM $tabelaAcad
            WHERE NOT EXISTS (SELECT * FROM $tabelaSistec WHERE $tabelaAcad.nome = $tabelaSistec.nome && $tabelaSistec.ano_ciclo_inicial = $tabelaAcad.ano_letivo_ini && $tabelaSistec.periodo_ciclo_inicial = $tabelaAcad.periodo_letivo_ini);";
    $conn->query($sql);
}

function naoExisteAcad($conn, $tabelaAcad, $tabelaSistec, $tabelaNaoExisteAcad) {
    $sql = "INSERT INTO $tabelaNaoExisteAcad(nome,cpf,cod_sistec,nome_ciclo)
            SELECT $tabelaSistec.nome, $tabelaSistec.cpf, $tabelaSistec.cod_sistec, $tabelaSistec.no_ciclo_matricula
            FROM $tabelaSistec
            WHERE NOT EXISTS (SELECT * FROM $tabelaAcad WHERE $tabelaAcad.nome = $tabelaSistec.nome && $tabelaSistec.ano_ciclo_inicial = $tabelaAcad.ano_letivo_ini && $tabelaSistec.periodo_ciclo_inicial = $tabelaAcad.periodo_letivo_ini);";
    $conn->query($sql);
}

function soNumeros($str) {
    return preg_replace("/[^0-9]/", "", $str);
}

function completarCpf($str) {
    $cpfCompleto = str_pad($str, 11, "0", STR_PAD_LEFT);
    return $cpfCompleto;
}

function tudoMaiusculo($str) {
    $maiusculo = strtoupper($str);
    return $maiusculo;
}

function verificaCiclo($nomeCiclo, $separador, $cod) {
    $cont = substr_count($nomeCiclo, $separador);

    if ($cod === "2856") {
        if ($cont === 2) {
            $tratado = dataHora($nomeCiclo, $cont);
            $tamanho = strlen($tratado);

            if ($tamanho === 10) {
                $anoPeriodoTec = quebraAnoPeriodo($tratado);
            } else if ($tamanho === 14) {
                $anoPeriodoTec = quebraAnoPeriodoFP($tratado);
            } else {
                $anoPeriodoTec = ["0", "0", "0", "0"];
            }
            return $anoPeriodoTec;
        } else if ($cont === 3) {
            $tratado = dataHora($nomeCiclo, $cont);
            $tamanho = strlen($tratado);

            if ($tamanho === 10) {
                $anoPeriodoTec = quebraAnoPeriodo($tratado);
            } else if ($tamanho === 14) {
                $anoPeriodoTec = quebraAnoPeriodoFP($tratado);
            } else {
                $anoPeriodoTec = ["0", "0", "0", "0"];
            }
            return $anoPeriodoTec;
        } else if ($cont === 4) {
            $tratado = dataHora($nomeCiclo, $cont);
            $tamanho = strlen($tratado);

            if ($tamanho === 10) {
                $anoPeriodoTec = quebraAnoPeriodo($tratado);
            } else if ($tamanho === 14) {
                $anoPeriodoTec = quebraAnoPeriodoFP($tratado);
            } else {
                $anoPeriodoTec = ["0", "0", "0", "0"];
            }
            return $anoPeriodoTec;
        }
        //Cod = 143 - Cursos EAD
    } else if ($cod === "143") {
        if ($cont === 2) {
            $tratado = dataHora($nomeCiclo, $cont);
            $tamanho = strlen($tratado);

            if ($tamanho === 10) {
                $anoPeriodoTec = quebraAnoPeriodo($tratado);
            } else if ($tamanho === 14) {
                $anoPeriodoTec = quebraAnoPeriodoFP($tratado);
            } else {
                $anoPeriodoTec = ["0", "0", "0", "0"];
            }
            return $anoPeriodoTec;
        } else if ($cont === 3) {
            $tratado = dataHora($nomeCiclo, $cont);
            $tamanho = strlen($tratado);

            if ($tamanho === 10) {
                $anoPeriodoTec = quebraAnoPeriodo($tratado);
            } else if ($tamanho === 14) {
                $anoPeriodoTec = quebraAnoPeriodoFP($tratado);
            } else {
                $anoPeriodoTec = ["0", "0", "0", "0"];
            }
            return $anoPeriodoTec;
        } else if ($cont === 4) {
            $tratado = dataHora($nomeCiclo, $cont - 1);
            $tamanho = strlen($tratado);

            if ($tamanho === 10) {
                $anoPeriodoTec = quebraAnoPeriodo($tratado);
            } else if ($tamanho === 14) {
                $anoPeriodoTec = quebraAnoPeriodoFP($tratado);
            } else {
                $anoPeriodoTec = ["0", "0", "0", "0"];
            }
            return $anoPeriodoTec;
        } else if ($cont === 5) {
            $tratado = dataHora($nomeCiclo, $cont - 2);
            $tamanho = strlen($tratado);

            if ($tamanho === 10) {
                $anoPeriodoTec = quebraAnoPeriodo($tratado);
            } else if ($tamanho === 14) {
                $anoPeriodoTec = quebraAnoPeriodoFP($tratado);
            } else {
                $anoPeriodoTec = ["0", "0", "0", "0"];
            }
            return $anoPeriodoTec;
        }
    }
}

function quebraAnoPeriodoFP($tratado) {
    //AGO2013DEZ2017
    $periodoInicial = substr($tratado, 0, -11);
    $anoInicial = substr($tratado, 3, -7);
    $periodoConclusao = substr($tratado, 7, -4);
    $anoConclusao = substr($tratado, 10);

    //Período Inicial
    if ($periodoInicial == "JAN" || $periodoInicial == "FEV" || $periodoInicial == "MAR" || $periodoInicial == "ABR" || $periodoInicial == "MAI" || $periodoInicial == "JUN") {
        $periodoInicial = 1;
    } else if ($periodoInicial == "JUL" || $periodoInicial == "AGO" || $periodoInicial == "SET" || $periodoInicial == "OUT" || $periodoInicial == "NOV" || $periodoInicial == "DEZ") {
        $periodoInicial = 2;
    } else {
        $periodoInicial = 0;
    }

    //Período Conclusão previsto
    if ($periodoConclusao == "JAN" || $periodoConclusao == "FEV" || $periodoConclusao == "MAR" || $periodoConclusao == "ABR" || $periodoConclusao == "MAI" || $periodoConclusao == "JUN") {
        $periodoConclusao = 1;
    } else if ($periodoConclusao == "JUL" || $periodoConclusao == "AGO" || $periodoConclusao == "SET" || $periodoConclusao == "OUT" || $periodoConclusao == "NOV" || $periodoConclusao == "DEZ") {
        $periodoConclusao = 2;
    } else {
        $periodoConclusao = 0;
    }

    return [$anoInicial, $periodoInicial, $anoConclusao, $periodoConclusao];
}

function quebraAnoPeriodo($tratado) {
    $anoInicial = substr($tratado, 0, -9);
    $periodoInicial = substr($tratado, 5, -7);
    $anoConclusao = substr($tratado, 7, -2);
    $periodoConclusao = substr($tratado, 12);
    return [$anoInicial, $periodoInicial, $anoConclusao, $periodoConclusao];
}

function dataHora($data, $posicao) {
    $partes = explode("-", (string) ($data));
    $parteData = $partes[$posicao];
    $saida = str_replace(" ", "", $parteData);
    $saida = str_replace(".", "", $saida);
    $saida = str_replace("/", "", $saida);

    return $saida;
}

function tratarNomes($valor) {
    $apostrofo = ["'"];
    //Remover acentos
    //$semAcentos = removerAcentos($valor);
    $comEspaco = $valor;
    //Remove espaços
    $semEspaco = rtrim($comEspaco);
    //Remove apostrofo
    $semApostrofo = str_replace($apostrofo, "", $semEspaco);
    //Deixa tudo maiusculo
    $maiusculo = tudoMaiusculo($semApostrofo);
    return $maiusculo;
}
?> 
<html>
    <head>                
        <meta charset="UTF-8">        
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="css/bootstrap.min.css">        
        <link rel="stylesheet" href="css/estilo.css">        
        <!-- jQuery library -->
        <script src="js/jquery-3.1.0.min.js"></script>
        <!-- Latest compiled JavaScript -->
        <script src="js/bootstrap.min.js"></script>  
        <script src="js/meujavascript.js"></script>  
    </head>
    <body>
        <div class="col-md-3">
            <form class="" role="form" method='post' enctype="multipart/form-data">
                <div class="form-group">
                    <label class="control-label" for="exampleInputPassword1">Arquivo do Qacadêmico</label>
                    <input type="file" size="100" multiple="multiple" name='sel_file' class="form-control">                  
                </div>                
                <button type="submit" name="submit_acad" class="btn btn-success">Enviar</button>                
                <button type="submit" name="sem_cpf_acad" class="btn btn-success">Alunos sem cpf</button>
                <button type="submit" name="exibir_acad" class="btn btn-danger">Exibir aluno</button>
                <button type="submit" name="purge_acad" class="btn btn-danger">Limpar Tabela</button>
            </form>               
        </div>

        <div class="col-md-3">
            <form class="" role="form" method='post' enctype="multipart/form-data">                
                <div class="form-group">
                    <label class="control-label" for="exampleInputPassword1">TURMAS SISTEC</label>
                    <input type="file" size="100" multiple="multiple" name='sel_file' class="form-control">
                </div>                
                <button type="submit" name="submit_curso_sistec" class="btn btn-success">Enviar</button>                                            
                <button type="submit" name="purge_curso_sistec" class="btn btn-danger">Limpar Tabela</button>
            </form>               
        </div> 

        <div class="col-md-3">
            <form class="" role="form" method='post' enctype="multipart/form-data">
                <div class="form-group">
                    <label class="control-label" for="exampleInputPassword1">Arquivo do Qacadêmico</label>
                    <input type="file" size="100" multiple="multiple" name='sel_file' class="form-control">                  
                </div>                
                <button type="submit" name="submit_acad" class="btn btn-success">Enviar</button>                
                <button type="submit" name="sem_cpf_acad" class="btn btn-success">Alunos sem cpf</button>
                <button type="submit" name="exibir_acad" class="btn btn-danger">Exibir aluno</button>
                <button type="submit" name="purge_acad" class="btn btn-danger">Limpar Tabela</button>
            </form>               
        </div>

        <div class="col-md-6">
            <form class="" role="form" method='post' enctype="multipart/form-data">                                              
                <button type="submit" name="sit_periodo" class="btn btn-success">Situação do Período Divergente</button>
                <button type="submit" name="exibir_sit_div" class="btn btn-danger">Exibir aluno</button>
                <button type="submit" name="purge_sit_periodo" class="btn btn-danger">Limpar Tabela</button>
            </form>  
            <form class="" role="form" method='post' enctype="multipart/form-data">                                                              
                <button type="submit" name="nome_diferentes" class="btn btn-success">Nomes Diferentes com mesmo CPF</button>
                <button type="submit" name="exibir_nome_dif" class="btn btn-danger">Exibir aluno</button>
                <button type="submit" name="purge_sit_periodo" class="btn btn-danger">Limpar Tabela</button>
            </form>
            <form class="" role="form" method='post' enctype="multipart/form-data">                                                              
                <button type="submit" name="cpf_divergente" class="btn btn-success">Mesma pessoa com cpf diferente</button>
                <button type="submit" name="exibir_cpf_div" class="btn btn-danger">Exibir aluno</button>
                <button type="submit" name="purge_cpf_divergente" class="btn btn-danger">Limpar Tabela</button>
            </form>           
            <form class="" role="form" method='post' enctype="multipart/form-data">                                              
                <button type="submit" name="nao_existe_sistec" class="btn btn-success">Aluno não existe no sistec</button> 
                <button type="submit" name="exibir_nao_existe_sistec" class="btn btn-danger">Exibir aluno</button>
                <button type="submit" name="purge_nao_existe_sistec" class="btn btn-danger">Limpar Tabela</button>
            </form>  
            <form class="" role="form" method='post' enctype="multipart/form-data">                                              
                <button type="submit" name="nao_existe_acad"class="btn btn-success">Aluno não existe no Qacadêmico</button>  
                <button type="submit" name="exibir_nao_existe_acad" class="btn btn-danger">Exibir aluno</button>
                <button type="submit" name="purge_nao_existe_acad" class="btn btn-danger">Limpar Tabela</button>
            </form>             
        </div>          
    </body>
</html>