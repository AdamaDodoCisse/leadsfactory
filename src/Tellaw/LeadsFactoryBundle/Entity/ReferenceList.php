<?php

namespace Tellaw\LeadsFactoryBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * Tellaw\LeadsFactoryBundle\Entity\ReferenceList
 *
 * 
 * @ORM\Entity
 */
class ReferenceList {

    /**
     * @ORM\OneToMany(targetEntity="Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement", mappedBy="referenceList", cascade={"persist"})
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
     * @param \Tellaw\LeadsFactoryBundle\Entity\longtext $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \Tellaw\LeadsFactoryBundle\Entity\longtext
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
     * @param \Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $elements
     * @return ReferenceList
     */
    public function addElement(\Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $elements)
    {
        $this->elements[] = $elements;

        return $this;
    }

    /**
     * Remove elements
     *
     * @param \Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $elements
     */
    public function removeElement(\Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement $elements)
    {
        $this->elements->removeElement($elements);
    }

    public function getJson () {

        $data = array();

        $elements = $this->getElements ();
        $data["lists"] = array( 0 => "l1", 1 => "l2", 3=>"l3" );
        $data["elements"] = $this->getChilds( $elements );

        //var_dump(($elements));
        //die();
        $json = json_encode( $data );

        return $json;
    }

    public function getChilds ( $elements ) {

        $dataChilds = array();

        foreach ($elements as $element) {


            if ( $element->getChildren()->count() ) {

                $dataChilds[] = array (
                    "id"=>$element->getId(),
                    "name"=>$element->getName(),
                    "value"=>$element->getValue(),
                    "childrens" => $this->getChilds( $element->getChildren() )

                );

            } else {

                $dataChilds[] = array (
                    "id"=>$element->getId(),
                    "name"=>$element->getName(),
                    "value"=>$element->getValue()

                );


            }
//var_dump ($element->getChildren());
            //if ( count($element->getChildren()) ) {



            //}

        }

        return $dataChilds;

    }

    public function setJson ( $json ) {



    }

}
