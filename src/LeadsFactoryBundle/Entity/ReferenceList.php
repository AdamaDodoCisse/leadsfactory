<?php

namespace LeadsFactoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 *
 * LeadsFactoryBundle\Entity\ReferenceList
 *
 *
 * @ORM\Entity(repositoryClass="LeadsFactoryBundle\Entity\ReferenceListRepository")
 */
class ReferenceList
{

    /**
     * @ORM\OneToMany(targetEntity="LeadsFactoryBundle\Entity\ReferenceListElement", mappedBy="referenceList", cascade={"persist"})
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $elements;

    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $code
     * @ORM\Column(type="string", nullable=true, name="code")
     */
    protected $code;

    /**
     * @var string $name
     * @ORM\Column(type="string", nullable=true, name="name")
     */
    protected $name;

    /**
     * @var longtext $description
     * @ORM\Column(type="text", nullable=true, name="description")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="LeadsFactoryBundle\Entity\Scope")
     * @ORM\JoinColumn(name="scope", referencedColumnName="id")
     */
    protected $scope;

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }


    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param \LeadsFactoryBundle\Entity\longtext $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \LeadsFactoryBundle\Entity\longtext
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return mixed
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Add elements
     *
     * @param \LeadsFactoryBundle\Entity\ReferenceListElement $elements
     * @return ReferenceList
     */
    public function addElement(\LeadsFactoryBundle\Entity\ReferenceListElement $elements)
    {
        $this->elements[] = $elements;

        return $this;
    }

    /**
     * Remove elements
     *
     * @param \LeadsFactoryBundle\Entity\ReferenceListElement $elements
     */
    public function removeElement(\LeadsFactoryBundle\Entity\ReferenceListElement $elements)
    {
        $this->elements->removeElement($elements);
    }

    public function getJson($referingLists)
    {

        $data = array();

        $elements = $this->getElements();
        //var_dump(($elements));

        $data["lists"] = $referingLists;
        $data["elements"] = $this->getChilds($elements);

//        var_dump(($elements));
//        die();
        $json = json_encode($data, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $json;
    }

    public function getChilds($elements)
    {

        $dataChilds = array();

        foreach ($elements as $element) {


            if ($element->getChildren()->count()) {

                $dataChilds[] = array(
                    "id" => $element->getId(),
                    "name" => $element->getName(),
                    "value" => $element->getValue(),
                    "childrens" => $this->getChilds($element->getChildren())

                );

            } else {

                $dataChilds[] = array(
                    "id" => $element->getId(),
                    "name" => $element->getName(),
                    "value" => $element->getValue()

                );


            }
            //var_dump ($element->getChildren());
            //if ( count($element->getChildren()) ) {


            //}

        }

        return $dataChilds;

    }

    public function setJson($json)
    {


    }

}
