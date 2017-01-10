-- Dados do discente
Select
    d.ano_ingresso, d.periodo_ingresso, d.matricula, d.status, d.data_colacao_grau, d.id_curriculo,
    p.nome, p.sexo, to_char(p.data_nascimento, 'DD/MM/YYYY') as nascimento, lpad(cast(p.cpf_cnpj as text), 11, '0') as cpf, p.email, p.telefone_celular,
    c.id_curso, c.nome, c.nivel, c.id_unidade_coordenacao, c.id_turno, c.codigo_inep, c.reconhecimentoportaria
From public.discente d
Inner Join comum.pessoa p on p.id_pessoa = d.id_pessoa
Inner Join public.curso c on c.id_curso = d.id_curso
Where d.ano_ingresso = '2016' and c.nivel = 'G'
Limit 100;
