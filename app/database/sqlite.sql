-- Instruções para criar o banco de dados SQLite
-- 1. Abra o terminal ou prompt de comando. 
-- 2. Navegue até o diretório onde deseja criar o banco de dados.
--    .\app\database\
-- 3. Execute o seguinte comando para criar um novo banco de dados SQLite:
--    sqlite3 sqlite.db
-- 4. Após criar o banco de dados, você pode executar os comandos SQL abaixo para criar as tabelas e inserir os dados iniciais.

-- Tabela de Clientes
CREATE TABLE Clientes (
    id_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT,
    email TEXT,
    cidade TEXT,
    estado TEXT,
    data_cadastro DATE
);

-- Tabela de Pedidos
CREATE TABLE Pedidos (
    id_pedido INTEGER PRIMARY KEY AUTOINCREMENT,
    id_cliente INTEGER,
    data_pedido DATE,
    valor_total REAL,
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente)
);

-- Tabela de Produtos
CREATE TABLE Produtos (
    id_produto INTEGER PRIMARY KEY AUTOINCREMENT,
    produto TEXT,
    preco_unitario REAL,
    quantidade_estoque INTEGER
);

-- Tabela de Itens de Pedido
CREATE TABLE Itens_Pedido (
    id_item INTEGER PRIMARY KEY AUTOINCREMENT,
    id_pedido INTEGER,
    id_produto INTEGER,
    quantidade INTEGER,
    preco_unitario REAL,
    FOREIGN KEY (id_pedido) REFERENCES Pedidos(id_pedido),
    FOREIGN KEY (id_produto) REFERENCES Produtos(id_produto)
);

-- Inserindo Clientes
INSERT INTO Clientes (nome, email, cidade, estado, data_cadastro) VALUES
('Harry Potter', 'harry@hogwarts.edu', 'Godricks Hollow', 'GH', '2023-01-15'),
('Hermione Granger', 'hermione@hogwarts.edu', 'Londres', 'LD', '2023-02-10'),
('Ron Weasley', 'ron@hogwarts.edu', 'Toca', 'TK', '2023-03-05'),
('Draco Malfoy', 'draco@hogwarts.edu', 'Malfoy Manor', 'MM', '2023-04-12');

-- Inserindo Produtos
INSERT INTO Produtos (produto, preco_unitario, quantidade_estoque) VALUES
('Pastilhas Vomitantes', 12.00, 100),
('Orelhas Extensíveis', 25.00, 50),
('Pó Escurecedor Instantâneo', 45.00, 30),
('Kit Mata-Aula', 35.00, 40),
('Chapéu Falante de Brinquedo', 75.00, 20),
('Canarinho Instantâneo', 18.00, 60);

-- Inserindo Pedidos
INSERT INTO Pedidos (id_cliente, data_pedido, valor_total) VALUES
(1, '2024-05-20', 57.00),
(2, '2024-06-10', 80.00),
(3, '2024-07-01', 25.00),
(1, '2025-01-15', 45.00),
(4, '2024-08-22', 75.00);

-- Inserindo Itens_Pedido
INSERT INTO Itens_Pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES
(1, 1, 2, 12.00),  -- Harry comprou 2 Pastilhas Vomitantes
(1, 2, 1, 25.00),  -- Harry comprou 1 Orelha Extensível
(2, 4, 2, 35.00),  -- Hermione comprou 2 Kits Mata-Aula
(2, 6, 1, 18.00),  -- Hermione comprou 1 Canarinho
(3, 2, 1, 25.00),  -- Ron comprou 1 Orelha Extensível
(4, 3, 1, 45.00),  -- Harry comprou 1 Pó Escurecedor
(5, 5, 1, 75.00);  -- Draco comprou 1 Chapéu Falante
