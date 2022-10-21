<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Entity\Provincia;
use App\Form\ProvinciaType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ProvinciaController extends AbstractController
{
    #[Route("/provincia/nueva", name: "nueva_provincia")]
    public function nueva(ManagerRegistry $doctrine, Request $request): Response
    {
        $provincia = new Provincia();
        $formulario = $this->createForm(ProvinciaType::class, $provincia);
        $formulario->handleRequest($request);
    
        //No funciona
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $provincia = $formulario->getData();
            $repositorio = $doctrine->getRepository(Provincia::class);
            if ($repositorio->findOneByNombre($provincia->getNombre())) {
                return new Response("La provincia introducida ya existe.");
            } else {
                $entityManager = $doctrine->getManager();
                try {
                    $entityManager->persist($provincia);
                    $entityManager->flush();
                    return $this->render("ficha_provincia.html.twig", array(
                        "provincia" => $provincia,
                        "numContactos" => 0
                    ));
                } catch (\Exception $e) {
                    return new Response("No se ha podido añadir la provincia a la base de datos.");
                }
            }
        }

        return $this->render("nueva_provincia.html.twig", array(
            "formulario" => $formulario->createView()
        ));
    }

    #[Route("/provincia/{texto}", name: "buscar_provincia")]
    public function buscar(ManagerRegistry $doctrine, $texto): Response
    {
        $repositorio = $doctrine->getRepository(Provincia::class);
        $provincia = $repositorio->findOneByNombre($texto);

        if ($provincia) {
            $repositorioContacto = $doctrine->getRepository(Contacto::class);
            $contactos = $repositorioContacto->findByProvincia($provincia->getId());
            $numContactos = count($contactos);
        } else {
            $numContactos = null;
        }

        return $this->render("ficha_provincia.html.twig", array(
            "provincia" => $provincia,
            "numContactos" => $numContactos
        ));
    }
}
