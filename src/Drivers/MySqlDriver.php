<?php

namespace Balambasik\Throttler\Drivers;

use Balambasik\Throttler\DriverInterface;

class MySqlDriver extends AbstractDriver implements DriverInterface
{
    /**
     * @var string
     */
    private $table_name;

    /**
     * @var \PDO
     */
    private $db;

    /**
     * @param string $db_name
     * @param string $db_host
     * @param string $db_user
     * @param string $db_password
     * @param string $table_name
     * @throws \Exception
     */
    public function __construct(string $db_host, string $db_user, string $db_password, string $db_name, string $table_name)
    {
        $this->table_name = $table_name;

        try {
            $this->db = new \PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_password);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param int $timestamp
     * @param array $tags
     * @return void
     */
    public function clearHitsLessThan(int $timestamp, array $tags = []): void
    {
        if ($tags) {
            $tags  = implode(",", $tags);
            $query = $this->db->prepare("DELETE FROM {$this->table_name} WHERE `wait` < :wait AND WHERE `tag` IN('$tags');");
        } else {
            $query = $this->db->prepare("DELETE FROM {$this->table_name} WHERE `wait` < :wait;");
        }

        $query->bindValue(":wait", $timestamp, \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * @param string $identifier
     * @param string $tag
     * @param int $timestamp
     * @return void
     */
    public function setHit(string $identifier, string $tag, int $timestamp): void
    {
        if ($this->getLastHitTimestamp($identifier, $tag)) {
            $query = $this->db->prepare("UPDATE {$this->table_name} SET `wait` = :wait WHERE `id` = :id AND `tag` = :tag;");
        } else {
            $query = $this->db->prepare("INSERT INTO {$this->table_name} (`id`, `tag`, `wait`) VALUES (:id, :tag, :wait);");
        }

        $query->bindValue(":id", $this->getHash($identifier), \PDO::PARAM_STR);
        $query->bindValue(":tag", $this->getHash($tag), \PDO::PARAM_STR);
        $query->bindValue(":wait", $timestamp, \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * @param string $identifier
     * @param string $tag
     * @return int
     */
    public function getLastHitTimestamp(string $identifier, string $tag): int
    {
        $query = $this->db->prepare("SELECT `wait` FROM {$this->table_name} WHERE `id` = :id AND `tag` = :tag;");
        $query->bindValue(":id", $this->getHash($identifier), \PDO::PARAM_STR);
        $query->bindValue(":tag", $this->getHash($tag), \PDO::PARAM_STR);
        $query->execute();
        $row = $query->fetch(\PDO::FETCH_ASSOC);
        return $row ? reset($row) : 0;
    }

    /**
     * @return void
     */
    public function createTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS `{$this->table_name}` (`id` varchar(10), `tag` varchar(10), `wait` INT(11) UNSIGNED NOT NULL);");
        $this->db->exec("ALTER TABLE `{$this->table_name}` ADD INDEX (`id`, `tag`);");
    }

    /**
     * @return DriverInterface
     */
    public function clear(): DriverInterface
    {
        $this->db->exec("DELETE FROM {$this->table_name};");
        return $this;
    }


}
