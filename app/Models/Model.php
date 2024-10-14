<?php

class Model {
    protected $table;
    private $conn;

    // Estabele a conexão com o banco de dados a partir de variáveis globais
    public function __construct() {
        global $dbHost, $dbName, $dbUser, $dbPassword;
        try {
            $this->conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
        }
    }

    // Salva dados do objeto no banco de dados, seja para criação o atualização de dados
    public function save() {
        $attributes = get_object_vars($this);

        if (array_key_exists('updated_at', $attributes)) {
            $attributes['updated_at'] = date("Y-m-d H:i:s");
        }

        unset($attributes['table'], $attributes['conn']);

        // Atualiza registro
        if (isset($this->id)) {
            $setClause = [];
            foreach ($attributes as $key => $value) {
            $setClause[] = "$key = :$key";
            }
            $setClause = implode(", ", $setClause);
            
            $sql = "UPDATE " . $this->table . " SET $setClause WHERE id = :id";

            try {
                $stmt = $this->conn->prepare($sql);
                foreach ($attributes as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->bindValue(":id", $this->id);
                $stmt->execute();

                return $this->find();
            } catch (PDOException $e) {
                echo "Erro ao atualizar dados: " . $e->getMessage();
                return false;
            }
        }

        if (array_key_exists('created_at', $attributes)) {
            $attributes['created_at'] = date("Y-m-d H:i:s");
        }

        // Insere registro
        $columns = implode(", ", array_keys($attributes));
        $values = ":" . implode(", :", array_keys($attributes));

        $sql = "INSERT INTO " . $this->table . " ($columns) VALUES ($values)";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($attributes as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
        
            $this->id = $this->conn->lastInsertId();
            return $this->find();
        } catch (PDOException $e) {
            echo "Erro ao salvar dados: " . $e->getMessage();
            return false;
        }
    }

    // Busca um registro no banco de dados
    public function find($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":id", $id);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $object = new self();
                foreach ($data as $key => $value) {
                    $object->$key = $value;
                }
                return $object;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            echo "Erro ao buscar dados: " . $e->getMessage();
            return false;
        }
    }

    // Deleta um registro no banco de dados, seja com soft delete, através de um update ou hard delete, com o comando delete do mysql
    public function delete() {
        $attributes = get_object_vars($this);

        if (array_key_exists('deleted_at', $attributes)) {
            $attributes['deleted_at'] = date("Y-m-d H:i:s");
            return $this->save();
        }

        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Erro ao excluir dados: " . $e->getMessage();
            return false;
        }
    }
}
