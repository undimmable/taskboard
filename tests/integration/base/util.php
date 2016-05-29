<?php

namespace Taskboards;
/**
 * @author dimyriy
 * @version 1.0
 */
class Util
{
    /**
     * @var array
     */
    private $collected_entities = [];

    /**
     * @var \mysqli
     */
    private $connection;

    /**
     * Util constructor.
     * @param $mysql \mysqli
     */
    public function __construct($mysql)
    {
        $this->connection = $mysql;
        $collect = [
            "db_account.account" => "user_id",
            "db_user.user" => "id",
            "db_tx.tx" => "id",
            "db_task.task" => "id",
            "db_login.login" => "user_id",
            "db_text_idx.text_idx" => "entity_id",
            "db_user_info.user_info" => "user_id"
        ];
        try {
            foreach ($collect as $collect_db => $collect_column) {
                $this->collectState($mysql, $collect_db, $collect_column);
            }
        } catch (\Exception $e) {
            echo $e->getTraceAsString();
        }
    }

    /**
     * @param $connection \mysqli
     * @param $db \string
     * @param $column \string
     */
    private function collectState($connection, $db, $column)
    {
        $result = $connection->query("SELECT COALESCE(max($column), -1) as last_id FROM $db");
        $last_id = -1;
        if ($result->num_rows > 0) {
            $last_id = $result->fetch_row()[0];
        }
        if (!array_key_exists($db, $this->collected_entities)) {
            $this->collected_entities[$db] = [];
        }
        $this->collected_entities[$db][$column] = $last_id;
    }


    /**
     * @param $email \string
     * @param $password \string
     * @param $role \integer
     * @param $confirmed \bool
     * @return int
     */
    public function createUser($email, $password, $role, $confirmed)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $confirmation_token = hash("sha256", $email . 'confirmation_secret');
        $mysqli_stmt = $this->connection->prepare("INSERT INTO db_user.user (email, hashed_password, role, confirmation_token, confirmed) VALUES (?, ?, ?, ?, ?)");
        $confirmed_int = (integer)$confirmed;
        $mysqli_stmt->bind_param("ssisi", $email, $hashed_password, $role, $confirmation_token, $confirmed_int);
        $mysqli_stmt->execute();
        $id = $mysqli_stmt->insert_id;
        $mysqli_stmt->close();
        return $id;
    }

    /**
     * @param $user_id        \integer
     * @param $balance        \integer
     * @param $locked_balance \integer
     * @return int
     */
    public function createAccount($user_id, $balance, $locked_balance)
    {
        $mysqli_stmt = $this->connection->prepare("INSERT INTO db_account.account (user_id, balance, last_tx_id, locked_balance) VALUES (?, ?, -1, ?)");
        $mysqli_stmt->bind_param("idd", $user_id, $balance, $locked_balance);
        $mysqli_stmt->execute();
        $id = $mysqli_stmt->insert_id;
        $mysqli_stmt->close();
        return $id;
    }

    /**
     * Delete all entities created by this class from database
     */
    public function deleteAllCreatedEntities()
    {
        foreach ($this->collected_entities as $entity_type => $key_value) {
            foreach ($key_value as $key => $last_value) {
                /** @noinspection SqlResolve */
                $this->connection->query("delete from $entity_type WHERE $key > $last_value");
            }
        }
    }
}