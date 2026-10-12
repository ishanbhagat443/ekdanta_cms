<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Support\ServiceProvider;

class SectionTypeServiceProvider extends ServiceProvider
{
    /**
     * Every builder block available in the CMS. New blocks: create the
     * class + a frontend blade named after its key — nothing else.
     *
     * @var list<class-string<\App\Services\SectionTypes\SectionType>>
     */
    private array $types = [
        \App\Services\SectionTypes\Header\LogoSection::class,
        \App\Services\SectionTypes\Header\TopBarSection::class,
        \App\Services\SectionTypes\Header\AnnouncementBarSection::class,
        \App\Services\SectionTypes\Header\NavMenuSection::class,
        \App\Services\SectionTypes\Header\CtaButtonSection::class,
        \App\Services\SectionTypes\Header\SearchSection::class,
        \App\Services\SectionTypes\Header\LanguageSwitcherSection::class,
        \App\Services\SectionTypes\Shared\CustomHtmlSection::class,
        \App\Services\SectionTypes\Footer\FooterAboutSection::class,
        \App\Services\SectionTypes\Footer\FooterLinksSection::class,
        \App\Services\SectionTypes\Footer\FooterContactSection::class,
        \App\Services\SectionTypes\Footer\NewsletterSection::class,
        \App\Services\SectionTypes\Footer\SocialIconsSection::class,
        \App\Services\SectionTypes\Footer\CopyrightSection::class,
        \App\Services\SectionTypes\Footer\FooterFormSection::class,
        \App\Services\SectionTypes\Footer\InstagramFeedSection::class,
        // Ekdanta section pack ("Ekdanta · …" in Add a section).
        \App\Services\SectionTypes\Ekdanta\HomeHeroSection::class,
        \App\Services\SectionTypes\Ekdanta\ObstaclesSection::class,
        \App\Services\SectionTypes\Ekdanta\WorkShowcaseSection::class,
        \App\Services\SectionTypes\Ekdanta\ReelsCarouselSection::class,
        \App\Services\SectionTypes\Ekdanta\BrandShelvesSection::class,
        \App\Services\SectionTypes\Ekdanta\ServicesPanelsSection::class,
        \App\Services\SectionTypes\Ekdanta\FoundersStorySection::class,
        \App\Services\SectionTypes\Ekdanta\FinaleSection::class,
        \App\Services\SectionTypes\Ekdanta\ContactHeroSection::class,
        \App\Services\SectionTypes\Ekdanta\ContactChannelsSection::class,
        \App\Services\SectionTypes\Ekdanta\BriefBuilderSection::class,
        \App\Services\SectionTypes\Ekdanta\VisitUsSection::class,
        \App\Services\SectionTypes\Ekdanta\FaqDeckSection::class,
        \App\Services\SectionTypes\Ekdanta\ServicesHeroSection::class,
        \App\Services\SectionTypes\Ekdanta\ExpertiseCardsSection::class,
        \App\Services\SectionTypes\Ekdanta\ServiceDeepDiveSection::class,
        \App\Services\SectionTypes\Ekdanta\ProcessPathSection::class,
        \App\Services\SectionTypes\Ekdanta\ProofCarouselSection::class,
        \App\Services\SectionTypes\Ekdanta\PricingSection::class,
        \App\Services\SectionTypes\Ekdanta\ThankYouHeroSection::class,
        \App\Services\SectionTypes\Ekdanta\NextStepsSection::class,
        \App\Services\SectionTypes\Ekdanta\SaveOurNumberSection::class,
        \App\Services\SectionTypes\Ekdanta\WhileYouWaitSection::class,
        \App\Services\SectionTypes\Page\HeroSection::class,
        \App\Services\SectionTypes\Page\BannerSection::class,
        \App\Services\SectionTypes\Page\CtaSection::class,
        \App\Services\SectionTypes\Page\FaqSection::class,
        \App\Services\SectionTypes\Page\TimelineSection::class,
        \App\Services\SectionTypes\Page\GallerySection::class,
        \App\Services\SectionTypes\Page\TeamSection::class,
        \App\Services\SectionTypes\Page\CounterSection::class,
        \App\Services\SectionTypes\Page\FeaturesSection::class,
        \App\Services\SectionTypes\Page\TestimonialsSection::class,
        \App\Services\SectionTypes\Page\VideoSection::class,
        \App\Services\SectionTypes\Page\MapSection::class,
        \App\Services\SectionTypes\Page\AccordionSection::class,
        \App\Services\SectionTypes\Page\TabsSection::class,
        \App\Services\SectionTypes\Page\CardsSection::class,
        \App\Services\SectionTypes\Page\ImageBlockSection::class,
        \App\Services\SectionTypes\Page\TextBlockSection::class,
        \App\Services\SectionTypes\Page\SliderSection::class,
        \App\Services\SectionTypes\Page\FormSection::class,
        \App\Services\SectionTypes\Page\AboutCollageSection::class,
        \App\Services\SectionTypes\Page\ProcessStepsSection::class,
        \App\Services\SectionTypes\Page\WhyChooseSection::class,
        \App\Services\SectionTypes\Page\BlogPostsSection::class,
        \App\Services\SectionTypes\Page\AboutStorySection::class,
        \App\Services\SectionTypes\Page\FounderJourneySection::class,
        \App\Services\SectionTypes\Page\VideoShowcaseSection::class,
        \App\Services\SectionTypes\Page\ArtistrySection::class,
        \App\Services\SectionTypes\Page\HistoryTimelineSection::class,
        \App\Services\SectionTypes\Page\ContactDetailsSection::class,
        \App\Services\SectionTypes\Page\SeparatorSection::class,
    ];

    public function register(): void
    {
        $this->app->singleton(SectionTypeRegistry::class, function (): SectionTypeRegistry {
            $registry = new SectionTypeRegistry();

            foreach ($this->types as $class) {
                $registry->register(new $class());
            }

            return $registry;
        });
    }
}
