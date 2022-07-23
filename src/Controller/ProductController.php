<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, LoggerInterface $logger, ValidatorInterface $validator)
    {
        //get user email
        $userName = $this->getUser()->getUserIdentifier();

        //build form
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

            // get data from form
            $data = $form->getData();

            $entityManager = $doctrine->getManager();

            $product = new Product;
            /**
             * Validate
             */
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response($errorsString);
            }

            //get user email
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

            //result message
            $this->addFlash(
                'succes',
                'Your product was added'
            );

            //log
            $logger->info("$userName added $productName");

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // get all product list
        $products = $productRepository->findAll();

        //log
        $logger->info("$userName displayed a list of products");

        // render form and products
        return $this->render('product/product.html.twig', [
            'form' => $form->createView(),
            'products' => $products
        ]);
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, LoggerInterface $logger, ValidatorInterface $validator)
    {
        // get id from route param
        $id = $request->get('id');

        // product validation
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        //build form
        $form = $this->createFormBuilder()
            ->add('product_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter new product name'
                ]
            ])
            ->add('price', NumberType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter new price'
                ]
            ])
            ->add('edit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // get data from form
            $data = $form->getData();

            //get user email
            $userName = $this->getUser()->getUserIdentifier();

            if ((string)$product->getOwnerName() == (string)$userName) {
                $entityManager = $doctrine->getManager();
                $product->setProductName($data['product_name']);
                $product->setPrice($data['price']);
                $entityManager->persist($product);
                $entityManager->flush();

                //result message
                $this->addFlash(
                    'succes',
                    'Your product was edited'
                );

                //log
                $logger->info("$userName edited product id $id");

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            } else {
                //result message
                $this->addFlash(
                    'succes',
                    'You cannot edit a product that is not yours'
                );

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }
        }

        return $this->render('product/product_edit.html.twig', [
            'form' => $form->createView(),
            'product_id' => $id
        ]);
    }

    #[Route('/product/opinion/{id}', name: 'app_product_opinion')]
    public function opinion(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, LoggerInterface $logger, ValidatorInterface $validator)
    {
        // get id from route param
        $id = $request->get('id');

        // product validation
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        //build form
        $form = $this->createFormBuilder()
            ->add('opinion', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter new opinion for this product'
                ]
            ])
            ->add('add', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // get data from form
            $data = $form->getData();

            //get user email
            $userName = $this->getUser()->getUserIdentifier();

            if ((string)$product->getOwnerName() != (string)$userName) {
                $entityManager = $doctrine->getManager();

                $newProductOpinion = (string)$data['opinion'];
                $oldProductOpinioon = (string)$product->getOpinions();
                $productOpinion = $oldProductOpinioon . ' • ' . $newProductOpinion;

                $product->setOpinions($productOpinion);

                $entityManager->persist($product);
                $entityManager->flush();

                //result message
                $this->addFlash(
                    'succes',
                    'Successfully added reviews'
                );

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            } else {
                //result message
                $this->addFlash(
                    'succes',
                    'You cannot add a review to your product'
                );

                //log
                $logger->info("$userName added reviews to product id $id");

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }
        }

        return $this->render('product/product_opinion.html.twig', [
            'form' => $form->createView(),
            'product_id' => $id
        ]);
    }
}
