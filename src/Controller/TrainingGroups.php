<?php
namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("TrainingGroups::", methods: ["GET"])]
class TrainingGroups extends AbstractController
{
    #[Route("/internal/traininggroups", name: "get")]
    #[Template("traininggroups.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function get(): array
    {
        return json_decode(file_get_contents(sprintf("%s/traininggroups.json", MODELS_ROOT)));
    }
}