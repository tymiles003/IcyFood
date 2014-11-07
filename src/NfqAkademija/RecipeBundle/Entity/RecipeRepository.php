<?php

namespace NfqAkademija\RecipeBundle\Entity;

use Doctrine\ORM\EntityRepository;


/**
 * RecipeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RecipeRepository extends EntityRepository
{
    public function getOrderedByIngredients($ingredients)
    {
        $em = $this->getEntityManager();

        // get recipes with at least one ingredient from the $ingredients array
        $query = $em->createQuery("

            SELECT r, COUNT(i.id)/SIZE(r.ingredients) koef
            FROM RecipeBundle:Recipe r
            LEFT JOIN r.ingredients ri
            LEFT JOIN ri.ingredient i
            WHERE i.name IN (:ing)
            GROUP BY r
            ORDER BY koef DESC

        ")->setParameter('ing', $ingredients);

        $goodRecipes = $query->getResult();

        // put recipes' ids to an $goodIDs
        $goodIDs = [];
        foreach($goodRecipes as $recipe){
            $goodIDs[] = $recipe[0]->getId();

        }

        // get recipes that were not taken by the first query
        $query = $em->createQuery("

            SELECT r, 0 koef
            FROM RecipeBundle:Recipe r
            WHERE r.id NOT IN (:ids)

        ")->setParameter('ids', $goodIDs);

        $otherRecipes = $query->getResult();

        // merge both recipe-lists to one
        $recipes = array_merge($goodRecipes, $otherRecipes);

        return $recipes;
    }
}
