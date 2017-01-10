-- Dados do discente
Select
    d.matricula,
    p.nome, p.sexo, to_char(p.data_nascimento, 'DD/MM/YYYY') as nascimento, lpad(cast(p.cpf_cnpj as text), 11, '0') as cpf,
    c.nome as curso, c.nivel,
    s.descricao as status
    -- count(*)
From public.discente d
Inner Join comum.pessoa p on p.id_pessoa = d.id_pessoa
Inner Join public.curso c on c.id_curso = d.id_curso
Inner Join public.status_discente s on s.status = d.status
Where c.nivel = 'G' and d.status in (1, 5, 8, 9, 14) and d.ano_ingresso != '2017'
Order By p.nome


-- SELECT * FROM PUBLIC.STATUS_DISCENTE ORDER BY STATUS
-- status	descricao	situacao_vinculo
-- 1	-1	DESCONHECIDO	null
-- 2	1	ATIVO	null
-- 3	2	CADASTRADO	null
-- 4	3	DIPLOMADO	null
-- 5	5	TRANCADO	null
-- 6	6	EXCLUÍDO	null
-- 7	8	ATIVO - FORMANDO	null
-- 8	9	ATIVO - GRADUANDO	null
-- 9	10	NÃO CADASTRADO	null
-- 10	11	EM HOMOLOGAÇÃO	null
-- 11	12	DEFENDIDO	null
-- 12	13	PENDENTE DE CADASTRO	null
-- 13	14	ATIVO - DEPENDÊNCIA	null
-- 14	15	PRÉ-CADASTRADO	null

-- Entregas
-- graduação ()
-- eagro
-- prppg
-- ensino basico (cap)
