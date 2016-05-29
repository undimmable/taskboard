<?php

/**
 * @author dimyriy
 * @version 1.0
 */
class Util
{
    /**
     * @var array
     */
    private $created_entities = [];

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
    }


    /**
     * @param $email string
     * @param $password string
     * @param $role integer
     */
    public function createUser($email, $password, $role)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $confirmation_token = create_confirmation_token($email);
        $mysqli_result = $this->connection->query("INSERT INTO db_user.user (email, hashed_password, role, confirmation_token, confirmed) VALUES ($email, $hashed_password, $role, $confirmation_token, FALSE)");
        $this->putCreatedEntity("db_user.user", $this->connection->insert_id);
        $mysqli_result->close();
    }

    private function putCreatedEntity($key, $id)
    {
        if (!array_key_exists($key, $this->created_entities)) {
            $this->created_entities[$key] = [];
        }
        $this->created_entities[$key][] = $id;
    }

    /**
     * Delete all entities created by this class from database
     */
    public function deleteAllCreatedEntities()
    {
        foreach ($this->created_entities as $entity_type => $id_array) {
            /** @noinspection SqlResolve */
            $this->connection->query("delete from $entity_type WHERE id in (" . implode(',', $id_array) . ");");
        }
    }
}