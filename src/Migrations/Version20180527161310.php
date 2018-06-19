<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180527161310 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, album_id INT DEFAULT NULL, sous_album_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, messages LONGTEXT DEFAULT NULL, position INT DEFAULT NULL, INDEX IDX_14B784181137ABCF (album_id), INDEX IDX_14B7841819799F9A (sous_album_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sous_album (id INT AUTO_INCREMENT NOT NULL, album_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_467C6EA61137ABCF (album_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784181137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841819799F9A FOREIGN KEY (sous_album_id) REFERENCES sous_album (id)');
        $this->addSql('ALTER TABLE sous_album ADD CONSTRAINT FK_467C6EA61137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784181137ABCF');
        $this->addSql('ALTER TABLE sous_album DROP FOREIGN KEY FK_467C6EA61137ABCF');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B7841819799F9A');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE sous_album');
    }
}
