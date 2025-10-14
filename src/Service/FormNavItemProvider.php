<?php

namespace OHMedia\FormBundle\Service\Backend\Nav;

use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\FormBundle\Entity\Form;
use OHMedia\FormBundle\Security\Voter\FormVoter;

class FormNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        if ($this->isGranted(FormVoter::INDEX, new Form())) {
            return (new NavLink('Forms', 'form_index'))
                ->setIcon('ui-checks-grid');
        }

        return null;
    }
}
