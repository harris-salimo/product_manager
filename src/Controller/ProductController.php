<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/", name="app_product")
     */
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $productRepository->getPaginator($offset);
        return $this->render('product/index.html.twig', [
            'products' => $paginator,
            'previous' => $offset - $productRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $productRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    /**
     * @Route("/products/show/{id}", name="show_product")
     */
    public function show(Product $product): Response
    {
        $this->doesThisExist($product);

        return $this->render('product/show.html.twig', ['product' => $product]);
    }

    /**
     * @Route("/products/new", name="create_product")
     */
    public function create(Request $request, ManagerRegistry $doctrine, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $image = $form->get('image')->getData();
            if ($image) {
                $newFilename = $fileUploader->upload($image);

                $product->setImage($newFilename);
            }

            $product->setUser($this->getUser());

            $entityManager = $doctrine->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product');
        }


        return $this->renderForm('product/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/products/edit/{id}", name="edit_product")
     */
    public function edit(Request $request, ManagerRegistry $doctrine, int $id, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$this->isGrantedUserOwnerOf($product)) {
            throw new \Exception("Error Processing Request: the authenticated user cannot update this product", 1);
        }

        $this->doesThisExist($product);

        $product->setImage(new File($this->getParameter('images_directory') . '/' . $product->getImage()));

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $image = $form->get('image')->getData();
            if ($image) {
                $newFilename = $fileUploader->upload($image);

                $product->setImage($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('show_product', [
                'id' => $product->getId()
            ]);
        }


        return $this->renderForm('product/edit.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/products/delete/{id}", name="delete_product")
     */
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$this->isGrantedUserOwnerOf($product)) {
            throw new \Exception("Error Processing Request: the authenticated user cannot delete this product", 1);
        }

        $this->doesThisExist($product);

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('app_product');
    }

    private function doesThisExist($object): bool
    {
        if (!$object) {
            throw $this->createNotFoundException(
                'No product found for id ' . $object->getId()
            );
        }

        return true;
    }

    private function isGrantedUserOwnerOf($object): bool
    {
        return $this->getUser() === $object->getUser();
    }
}
