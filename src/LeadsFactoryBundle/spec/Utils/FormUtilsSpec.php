<?php

namespace spec\LeadsFactoryBundle\Utils;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use LeadsFactoryBundle\Entity\ReferenceListRepository;
use LeadsFactoryBundle\Utils\Fields\FieldFactory;

class FormUtilsSpec extends ObjectBehavior
{
    function let(ReferenceListRepository $reference_list_repository, Router $router, FieldFactory $field_factory)
    {
        $this->beConstructedWith($reference_list_repository, $router, $field_factory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('LeadsFactoryBundle\Utils\FormUtils');
    }

    function it_can_generate_a_form_key()
    {
        $this->setTime(new \DateTime('02-03-2015 16:32:32'));
        $this->getFormKey(12, 0)->shouldBe('031a07ab9399619c4f15892501191510');
    }

    function it_should_accept_a_key_from_the_current_hour()
    {
        $this->setTime(new \DateTime('02-03-2015 16:47:03'));
        $this->checkFormKey('031a07ab9399619c4f15892501191510', 12)->shouldBe(true);
    }

    function it_should_accept_a_key_from_the_previous_hour()
    {
        $this->setTime(new \DateTime('02-03-2015 17:12:57'));
        $this->checkFormKey('031a07ab9399619c4f15892501191510', 12)->shouldBe(true);
    }
}
