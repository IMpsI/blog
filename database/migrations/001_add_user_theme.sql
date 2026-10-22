ALTER TABLE usuarios
    ADD COLUMN tema_cor_principal CHAR(7) NULL AFTER status_solicitacao,
    ADD COLUMN tema_cor_secundaria CHAR(7) NULL AFTER tema_cor_principal;
