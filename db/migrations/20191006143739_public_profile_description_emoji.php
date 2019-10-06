<?php

use Phinx\Migration\AbstractMigration;

class PublicProfileDescriptionEmoji extends AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER TABLE afup_personnes_morales CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");

    }
}
