<?php
namespace App\Article\Entities;

use App\Utils\Entity;

class ArticleEntity extends Entity
{
    /**
     * @var int
     * @v optional
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var string
     */
    public $content;

    /**
     * @var int
     */
    public $author = 1;

    /**
     * @var int
     */
    public $article_product_id;

    /**
     * @var int
     */
    public $article_category_id = -1;

    /**
     * @var int
     */
    public $spiltway = 1;


    /**
     * @return int
     */
    public function getArticleCategoryId()
    {
        return $this->article_category_id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return int
     */
    public function getArticleProductId()
    {
        return $this->article_product_id;
    }

    /**
     * @return int
     */
    public function getSpiltway()
    {
        return $this->spiltway;
    }
}