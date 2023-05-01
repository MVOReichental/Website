<?php
namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: "PublicController::", methods: ["GET"])]
class PublicController extends AbstractController
{
    #[Route("/impressum", name: "getImprint")]
    #[Template("imprint.twig")]
    public function getImprint(): void
    {
    }

    #[Route("/beitreten", name: "getVereinBeitreten")]
    #[Template("verein/beitreten.twig")]
    public function getVereinBeitreten(): void
    {
    }

    #[Route("/beitreten/beitrittserklaerung.pdf", name: "getBeitrittserklaerung")]
    public function getBeitrittserklaerung(): Response
    {
        return $this->file(sprintf("%s/forms/Beitrittserklaerung.pdf", DATA_ROOT));
    }

    #[Route("/bisherige_dirigenten", name: "getVereinBisherigeDirigenten")]
    #[Template("verein/bisherige_dirigenten.twig")]
    public function getVereinBisherigeDirigenten(): void
    {
    }

    #[Route("/bisherige_erste_vorsitzende", name: "getVereinBisherigeErsteVorsitzende")]
    #[Template("verein/bisherige_erste_vorsitzende.twig")]
    public function getVereinBisherigeErsteVorsitzende(): void
    {
    }

    #[Route("/chronik", name: "getVereinChronik")]
    #[Template("verein/chronik.twig")]
    public function getVereinChronik(): void
    {
    }

    #[Route("/vereinsgeschichte", name: "getVereinVereinsgeschichte")]
    #[Template("verein/vereinsgeschichte.twig")]
    public function getVereinVereinsgeschichte(): void
    {
    }

    #[Route("/jugendausbildung/ausbildung_im_verein", name: "getJugendausbildung")]
    #[Template("jugendausbildung.twig")]
    public function getJugendausbildung(): void
    {
    }

    #[Route("/foerderverein/warum_foerderverein", name: "getFoerdervereinWarumFoerderverein")]
    #[Template("foerderverein/warum_foerderverein.twig")]
    public function getFoerdervereinWarumFoerderverein(): void
    {
    }

    #[Route("/foerderverein/kontakt", name: "getFoerdervereinContact")]
    #[Template("contact.twig")]
    public function getFoerdervereinContact(): array
    {
        return [
            "contacts" => json_decode(file_get_contents(sprintf("%s/foerderverein/contact.json", MODELS_ROOT)))
        ];
    }

    #[Route("/kontakt", name: "getContact")]
    #[Template("contact.twig")]
    public function getContact(): array
    {
        return [
            "contacts" => json_decode(file_get_contents(sprintf("%s/contact.json", MODELS_ROOT)))
        ];
    }
}