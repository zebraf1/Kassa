<?php

namespace App\Extensions\Query;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\SqlWalker;

class OldPassword extends Password
{
    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'OLD_PASSWORD('.$this->password->dispatch($sqlWalker).')';
    }
}
