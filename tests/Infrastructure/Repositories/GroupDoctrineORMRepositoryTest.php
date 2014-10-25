<?php namespace Cribbb\Tests\Infrastructure\Repositories;

use Illuminate\Support\Facades\App;
use Cribbb\Domain\Model\Groups\Name;
use Cribbb\Domain\Model\Groups\Slug;
use Cribbb\Domain\Model\Groups\Group;
use Cribbb\Domain\Model\Groups\GroupId;
use Illuminate\Support\Facades\Artisan;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Cribbb\Infrastructure\Repositories\GroupDoctrineORMRepository;
use Cribbb\Tests\Infrastructure\Repositories\Fixtures\GroupFixtures;

class GroupDoctrineORMRepositoryTest extends \TestCase
{
    /** @var GroupDoctrineORMRepository */
    private $repository;

    /** @var EntityManager */
    private $em;

    /** @var ORMExecutor */
    private $executor;

    /** @var Loader */
    private $loader;

    public function setUp()
    {
        parent::setUp();

        Artisan::call('doctrine:schema:create');

        $this->em         = App::make('Doctrine\ORM\EntityManagerInterface');
        $this->repository = new GroupDoctrineORMRepository($this->em);

        $this->executor = new ORMExecutor($this->em, new ORMPurger);
        $this->loader   = new Loader;
        $this->loader->addFixture(new GroupFixtures);
    }

    /** @test */
    public function should_return_next_identity()
    {
        $this->assertInstanceOf(
            'Cribbb\Domain\Model\Groups\GroupId', $this->repository->nextIdentity());
    }

    /** @test */
    public function should_find_name_by_name()
    {
        $this->executor->execute($this->loader->getFixtures());

        $name = new Name('Cribbb');
        $group = $this->repository->groupOfName($name);

        $this->assertInstanceOf('Cribbb\Domain\Model\Groups\Group', $group);
        $this->assertEquals($name, $group->name());
    }

    /** @test */
    public function should_find_group_by_slug()
    {
        $this->executor->execute($this->loader->getFixtures());

        $slug = new Slug('cribbb');
        $group = $this->repository->groupOfSlug($slug);

        $this->assertInstanceOf('Cribbb\Domain\Model\Groups\Group', $group);
        $this->assertEquals($slug, $group->slug());
    }

    /** @test */
    public function should_add_new_group()
    {
        $id   = GroupId::generate();
        $name = new Name('Cribbb');
        $slug = new Slug('cribbb');

        $this->repository->add(new Group($id, $name, $slug));

        $this->em->clear();

        $group = $this->repository->groupOfName(new Name('Cribbb'));

        $this->assertEquals($id,   $group->id());
        $this->assertEquals($name, $group->name());
        $this->assertEquals($slug, $group->slug());
    }
}