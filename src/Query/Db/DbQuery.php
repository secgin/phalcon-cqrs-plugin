<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use YG\Phalcon\Cqrs\Query\Query;


/**
 * @property \Phalcon\Db\Adapter\AdapterInterface                           $db
 * @property \Phalcon\Mvc\Model\Manager|\Phalcon\Mvc\Model\ManagerInterface $modelsManager
 */
abstract class DbQuery extends Query
{
}