<?php
namespace App\Controller;

use App\Entity\GroupMembersList;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: "Musicians::", methods: ["GET"])]
class Musicians extends AbstractController
{
    #[Route("/internal/musicians", name: "get")]
    #[Template("groupmembers.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function get(): array
    {
        return [
            "title" => "Musiker",
            "groups" => new GroupMembersList("musiker")
        ];
    }
}