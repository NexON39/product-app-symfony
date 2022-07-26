<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductAddType;
use App\Form\ProductEditType;
use App\Form\ProductOpinionType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\PriceGenerator;
use App\Service\ProductUserValidation;
use Symfony\Contracts\Translation\TranslatorInterface;

// brak testów (PHP)
class ProductController extends AbstractController
{
    public function formNotNullValidate($productName)
    {
        if ($productName == NULL) {
            return true;
        }
    }

    public function getCurrentUser()
    {
        try {
            $currentUser = $this->getUser()->getUserIdentifier();
            if (isset($currentUser)) {
                return $currentUser;
            } else {
                throw new Exception('User ' . $currentUser . ' not found');
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    #[Route('/product', name: 'app_product')]
    public function index(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, ValidatorInterface $validator, PriceGenerator $priceGenerator, TranslatorInterface $translator)
    {
        //get user email
        $userName = $this->getCurrentUser();

        // product init
        $product = new Product;

        // build form and handle request
        $form = $this->createForm(ProductAddType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // check if null
            if ($this->formNotNullValidate($form->get('productName')->getData())) {

                //result message
                $this->addFlash(
                    'succes',
                    $translator->trans('Product can not be null')
                );

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }

            $entityManager = $doctrine->getManager();

            $product->setOwnerName($userName);
            $product->setProductName($form->get('productName')->getData());
            $product->setPrice($priceGenerator->getProductPrice($form->get('productName')->getData()));

            $entityManager->persist($product);
            $entityManager->flush();

            //result message
            $this->addFlash(
                'succes',
                $translator->trans('Your product was added')
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
    public function edit(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, LoggerInterface $logger, ValidatorInterface $validator, ProductUserValidation $productUserValidation, TranslatorInterface $translator)
    {
        // get id from route param
        $id = $request->get('id');

        //get user email
        $userName = $this->getCurrentUser();

        // product validation
        // tu najlpiej by było od razu wyszukać po użytkowniku (DB)
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                $translator->trans('No product found for id') . ' ' . $id
            );
        }

        if ((string)$product->getOwnerName() != (string)$userName) {
            //result message
            $this->addFlash(
                'succes',
                $translator->trans('You cannot edit a product that is not yours')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // build form and handle request
        $form = $this->createForm(ProductEditType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // product user validate
            if ($productUserValidation->productEditValidate($product->getOwnerName(), $userName)) {

                $entityManager = $doctrine->getManager();

                $product->setProductName($form->get('productName')->getData());
                $product->setPrice($form->get('price')->getData());

                $entityManager->persist($product);
                $entityManager->flush();

                //result message
                $this->addFlash(
                    'succes',
                    $translator->trans('Your product was edited')
                );

                //log
                $logger->info("$userName edited product id $id");

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }
        }

        // form render
        return $this->render('product/product_edit.html.twig', [
            'form' => $form->createView(),
            'product_id' => $id
        ]);
    }

    #[Route('/product/opinion/{id}', name: 'app_product_opinion')]
    public function opinion(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, LoggerInterface $logger, ValidatorInterface $validator, ProductUserValidation $productUserValidation, TranslatorInterface $translator)
    {
        // get id from route param
        $id = $request->get('id');

        //get user email
        $userName = $this->getCurrentUser();

        // product validation
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                $translator->trans('No product found for id') . ' ' . $id
            );
        }

        // build form and handle request
        $form = $this->createForm(ProductOpinionType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // product user validate
            if ($productUserValidation->productOpinionValidate($product->getOwnerName(), $userName)) {

                $entityManager = $doctrine->getManager();

                $newProductOpinion = (string)$form->get('opinions')->getData();
                $oldProductOpinioon = (string)$product->getOpinions();

                // jeśli opinia będzie za długa, zostanie ucięta, bo mamy tylko 10000 znaków (DB)
                $productOpinion = $oldProductOpinioon . ' • ' . $newProductOpinion;

                $product->setOpinions($productOpinion);

                $entityManager->persist($product);
                $entityManager->flush();

                //result message
                $this->addFlash(
                    'succes',
                    $translator->trans('Successfully added reviews')
                );

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }

            //result message
            $this->addFlash(
                'succes',
                $translator->trans('You cannot add a review to your product')
            );

            //log
            $logger->info("$userName added reviews to product id $id");

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // form render
        return $this->render('product/product_opinion.html.twig', [
            'form' => $form->createView(),
            'product_id' => $id
        ]);
    }
}
