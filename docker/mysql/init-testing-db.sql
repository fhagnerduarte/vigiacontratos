-- Cria o banco de dados 'testing' para execucao de testes PHPUnit.
-- Este script e executado pelo MySQL na inicializacao do container.
CREATE DATABASE IF NOT EXISTS `testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `testing`.* TO 'sail'@'%';
FLUSH PRIVILEGES;
