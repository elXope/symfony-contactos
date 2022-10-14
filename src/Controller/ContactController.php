<?php
namespace App\Controller;

use App\Entity\Contacto;
use App\Entity\Provincia;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    private $contactos = [
        1 => ["nombre" => "Juan Pérez", "telefono" => "524142432", "email" => "juanp@ieselcaminas.org"],
        2 => ["nombre" => "Ana López", "telefono" => "58958448", "email" => "anita@ieselcaminas.org"],
        5 => ["nombre" => "Mario Montero", "telefono" => "5326824", "email" => "mario.mont@ieselcaminas.org"],
        7 => ["nombre" => "Laura Martínez", "telefono" => "42898966", "email" => "lm2000@ieselcaminas.org"],
        9 => ["nombre" => "Nora Jover", "telefono" => "54565859", "email" => "norajover@ieselcaminas.org"]
    ];    
    
    #[Route("/contacto/insertar", name:"insertar_contacto")]
    public function insertar(ManagerRegistry $doctrine): Response {
        $entityManager = $doctrine->getManager();
        foreach($this->contactos as $c) {
            $contacto = new Contacto();
            $contacto->setNombre($c["nombre"]);
            $contacto->setTelefono($c["telefono"]);
            $contacto->setEmail($c["email"]);
            $entityManager->persist($contacto);
        }

        try {
            $entityManager->flush();
            return new Response("Contactos insertados");
        } catch(\Exception $e) {
            return new Response("Error insertando objetos");
        }
    }

    #[Route("/contacto/{codigo<\d+>?1}", name:"ficha_contacto")]
    public function ficha(ManagerRegistry $doctrine, $codigo): Response {
      $repositorio = $doctrine->getRepository(Contacto::class);
      $contacto = $repositorio->find($codigo);
      
      return $this->render('ficha_contacto.html.twig', [
        "contacto" => $contacto
      ]);
    }

    #[Route("/contacto/buscar/{texto}", name:"buscar_contacto")]
    public function buscar(ManagerRegistry $doctrine, $texto): Response {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contactos = $repositorio->findByName($texto);

        return $this->render('lista_contactos.html.twig', [
            'contactos' => $contactos
        ]);
    }

    #[Route("/contacto/update/{id}/{columna}/{valor}", name:"modificar_contacto")]
    public function update(ManagerRegistry $doctrine, $id, $columna, $valor): Response {
        //Problema de fer-ho aixina: el email no el pots posar per a paràmetre a passar (pel .com etc)
        $entityManager = $doctrine->getManager();
        $columna = strtolower($columna);
        // $nombreColumnas = $entityManager->getClassMetadata("symfony-contactos\src\Entity\Contacto")->getColumnNames();
        if (in_array($columna, array("nombre", "telefono", "email"))) {
            $repositorio = $doctrine->getRepository(Contacto::class);
            $contacto = $repositorio->find($id);
            if($contacto) {
                switch ($columna) {
                    case "nombre":
                        $contacto->setNombre($valor);
                        break;
                    case "telefono":
                        $contacto->setTelefono($valor);
                        break;
                    case "email":
                        $contacto->setEmail($valor);
                        break;
                    default:
                        return new Response("Ací no entra en principi");
                }

                try {
                    $entityManager->flush();
                    return $this->render("ficha_contacto.html.twig", [
                        "contacto" => $contacto
                    ]);
                } catch (\Exception $e) {
                    return new Response("Error insertando objetos.");
                }
            } else {
                return $this-> render("ficha_contacto.html.twig", [
                    "contacto" => null
                ]);
            }
        } else {
            return new Response("No existe esa columna o está intentando modificar el 'id' y eso no se puede hacer.");
        }
    }

    #[Route("/contacto/delete/{id}", name:"eliminar_contacto")]
    public function delete(ManagerRegistry $doctrine, $id): Response {
        $entityManager = $doctrine->getManager();
        $repositorio = $entityManager->getRepository("Contacto");
        $contacto = $repositorio->find($id);
        if($contacto) {
            try {
                $entityManager->remove($contacto);
                $entityManager->flush();
                return new Response("Contacto eliminado");
            } catch (\Exception $e) {
                return new Response("No se ha podido eliminar el contacto.");
            }
        } else {
            return $this->render("ficha_contacto.html.twig", [
                "contacto" => null
            ]);
        }
    }

    #[Route("/contacto/insertarConProvincia", name:"insertar_con_provincia_contacto")]
    public function insertarConProvincia(ManagerRegistry $doctrine): Response {
        $entityManager = $doctrine->getManager();
        $provincia = new Provincia();

        $provincia->setNombre("Alicante");
        $contacto = new Contacto();

        $contacto->setNombre("Inserción de prueba con provincia");
        $contacto->setTelefono("1231231213");
        $contacto->setEmail("insercion.de.prueba@contacto.es");
        $contacto->setProvincia($provincia);

        try {
        $entityManager->persist($provincia);
        $entityManager->persist($contacto);
        $entityManager->flush();
        return $this->render("ficha_contacto.html.twig", [
            "contacto" => $contacto
        ]);
        } catch (\Exception $e) {
            return new Response("No se ha podido crear el contacto.");
        }
    }
}
