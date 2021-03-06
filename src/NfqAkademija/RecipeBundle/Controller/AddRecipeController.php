<?php

namespace NfqAkademija\RecipeBundle\Controller;

use NfqAkademija\RecipeBundle\Entity\Recipe;
use NfqAkademija\RecipeBundle\Form\RecipeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use NfqAkademija\RecipeBundle\Services\ScrapeService;

class AddRecipeController extends Controller
{
    /**
     * @Route("/part/form", name="new_recipe")
     * @Template()
     */
    public function newAction()
    {
        $recipe = new Recipe();
        $form = $this->createForm(new RecipeType(), $recipe, array(
            'action' => $this->generateUrl('new_recipe_submit'),
        ));

        return $this->render(
            'RecipeBundle:AddRecipe:new.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/form/naujas", name="new_recipe_simple")
     * @Template()
     */
    public function newSimpleAction()
    {
        $recipe = new Recipe();
        $form = $this->createForm(new RecipeType(), $recipe, array(
            'action' => $this->generateUrl('new_recipe_submit'),
        ));

        return $this->render(
            'RecipeBundle:AddRecipe:new_simple.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/form/naujas/submit", name="new_recipe_submit")
     * @Template()
     */
    public function submitAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RecipeType(), new Recipe());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $recipe = $form->getData();
            $recipesingredients = $recipe->getIngredients();
            foreach($recipesingredients as $recipeingredient){
                $oldIngredient = $em->getRepository('RecipeBundle:Ingredient')->findOneBy([
                    'name' => $recipeingredient->getIngredient()->getName()
                ]);

                if($oldIngredient){
                    $recipe->removeIngredient($recipeingredient);
                    $recipeingredient->setIngredient($oldIngredient);
                    $recipe->addIngredient($recipeingredient);
                }
            }

            $em->persist($recipe);
            $em->flush();

            return $this->render('RecipeBundle:AddRecipe:newok.html.twig');
        }

        return $this->render(
            'RecipeBundle:AddRecipe:new.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/form/crawler/{url}", name="crawler", requirements={"url" = ".+"})
     * @Template()
     */
    public function crawlAction($url)
    {
        $rcp = new ScrapeService($url);

        return new Response(var_export($rcp->getIngredients()));
    }
}
