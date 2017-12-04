-- Crítica Ano e Períodos não Correspondem À Entrada em Processos Seletivos
-- Objetivo: Identificar os Ciclos de Matrícula dos Cursos cuja entrada (Ano.Período) não corresponde a uma entrada 
--           cadastrada nos Processos Seletivos.
-- Ciclo de Matrícula => Ano.Periodo / Curso  => independente do turno, ou seja, se houver algum processo seletivo 
-- para qualquer turno de um curso, também deverá existir o ciclo correspondente englobando os alunos de TODOS os turnos 
-- dessa mesma entrada do curso.

-- Ano_ciclo_inicial.Periodo_ciclo_inicial / Curso  

- FALTA TRATAR CURSO
- FALTA INCLUIR UNIDADE_IFPE
- FALTA TRATAR QUANDO O PROCESSO SELETIVO TENHA CADASTRADO OUTROS TURNOS PARA O MESMO CURSO

-- De forma mais detalhada:
-- Busca Ciclos de Matrícula cujos Ano e Período do Ciclo Inicial NÃO EXISTEM em Processos Seletivos. Se encontrar 
-- é porque o ciclo é referente a um Ano.Período que não houve entrada, logo precisa ser excluído, a não ser que o 
-- Processo Seletivo não tenha sido cadastrado, sendo necessário corrigir os dados!
--
SELECT 
 DISTINCT ano_ciclo_inicial, periodo_ciclo_inicial, no_ciclo_matricula
 FROM monitoramento_sistec.aluno_sistec SIS,
      processo_seletivo.curso_acad CUR
 WHERE 
     CUR.cod_curso_sistec = SIS.cod_curso AND
     
   NOT EXISTS
   
   ( SELECT "EXISTE" 
     FROM processo_seletivo.ano_tipo_processo_seletivo ATPS,
          processo_seletivo.processo_seletivo PS
     WHERE 
       PS.id_ano_tipo_processo_seletivo = ATPS.id -- condição JOIN
       AND  -- condições do NOT EXISTS para a tabela aluno_sistec 
       ATPS.ano = SIS.ano_ciclo_inicial AND
       ATPS.semestre = SIS.periodo_ciclo_inicial AND
       PS.cod_curso_acad = CUR.cod_curso_acad      -- FALTA TRATAR QUANDO O PROCESSO SELETIVO TENHA CADASTRADO OUTROS TURNOS PARA O MESMO CURSO
   );