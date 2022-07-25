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

// brak testów (PHP)
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

        // form powinien mieć data class określony, żeby zbindować formularz na encję (Symfony)
        // wtedy nie trzeba danych przepisywać z formularza, na encję (Symfony)
        // brak obsłużenia niepoprawnego formularza (PHP)
        if ($form->isSubmitted() && $form->isValid()) {

            // get data from form
            $data = $form->getData();

            $entityManager = $doctrine->getManager();

            $product = new Product;

            //get user email

            // naruszenie DRY! (PHP)
            // nieobsłużony wyjątek null pointer exception (PHP)
            $userName = $this->getUser()->getUserIdentifier();

            // mnóstwo kodu inline + niepotrzebne przypisania (PHP)
            $product->setOwnerName($userName);
            $product->setProductName($data['product_name']);

            // logika obliczania cen inline (PHP), nie w serwisie (Symfony)
            if (strlen($data['product_name']) % 2 == 0) {
                $product->setPrice(20);
            } else {
                $product->setPrice(10);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            //result message

            // brak translacji (Symfony)
            $this->addFlash(
                'succes',
                'Your product was added'
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // get all product list
        $products = $productRepository->findAll();

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

        // tu najlpiej by było od razu wyszukać po użytkowniku (DB)
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        //build form

        // brak danych do edycji w formularzu (Symfony)
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

            // exception (PHP)
            $userName = $this->getUser()->getUserIdentifier();

            // walidacja użytkownika inline (PHP), bez serwisu (Symfony)
            // edycja powinna być zablokowana wcześniej, przed renderowaniem (PHP)
            // gdyby formularz zawierał dane, to edytujący zobaczy wszystkie dane produktu, a nie powinien (PHP)
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
                // niepotrzebny else (PHP)

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

            // walidacja inline, DRY
            if ((string)$product->getOwnerName() != (string)$userName) {
                $entityManager = $doctrine->getManager();

                $newProductOpinion = (string)$data['opinion'];
                $oldProductOpinioon = (string)$product->getOpinions();

                // jeśli opinia będzie za długa, zostanie ucięta, bo mamy tylko 10000 znaków (DB)
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
            }

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

        return $this->render('product/product_opinion.html.twig', [
            'form' => $form->createView(),
            'product_id' => $id
        ]);
    }
}
