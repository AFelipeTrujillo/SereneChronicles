<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('slug')
            ->add('tags', TextType::class)
        ;

        $builder->get('tags')->addModelTransformer(new CallbackTransformer(
            function (PersistentCollection | ArrayCollection $tagsAsArray): string {
                if($tagsAsArray instanceof PersistentCollection) {
                    $tagsAsArray = array_map(function (Tag $tag){
                        return $tag->getName();
                    }, $tagsAsArray->toArray());
                } else {
                    $tagsAsArray = $tagsAsArray->toArray();
                }
                return implode(',', $tagsAsArray);
            },
            function ($tagsAsString): array {
                $aNewTags = explode(',', $tagsAsString);
                $aOTags = [];
                foreach ($aNewTags as $tag)
                {
                    if(!$oTag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => $tag])) {
                        $oTag = new Tag();
                        $oTag->setName($tag);
                    }
                    $aOTags[] = $oTag;
                }
                return $aOTags;
            }
        ));

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();
            /** @var Post $post */
            $post = $event->getData();

            $existingPost = $this->entityManager->getRepository(Post::class)->findOneBy(['slug' => $post->getSlug()]);

            if ($existingPost && $existingPost->getId() !== $post->getId()) $form->get('slug')->addError(new FormError('The slug is already in use.'));

            $post->setSlugValue();

        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
