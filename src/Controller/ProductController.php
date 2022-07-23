<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(Request $request, ManagerRegistry $doctrine)
    {
        // create form
        $form = $this->createFormBuilder()
            ->add('product_name', TextType::class, [
                'required' => true
            ])
            ->add('add', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        // add product
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager = $doctrine->getManager();

            $product = new Product;

            $userName = $this->getUser()->getUserIdentifier();
            $product->setOwnerName($userName);

            $productName = $data['product_name'];
            $product->setProductName($productName);

            $productNameLength = strlen($productName);
            if ($productNameLength % 2 == 0) {
                $product->setPrice(20);
            } else {
                $product->setPrice(10);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash(
                'succes',
                'Your product was added'
            );

            return $this->redirect($this->generateUrl('app_product'));
        }

        // render form
        return $this->render('product/product.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
