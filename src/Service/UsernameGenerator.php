<?php


namespace App\Service;


use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;


class UsernameGenerator
{

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * Lazily-loaded table of words, per adjective (+being).
     *
     * @var array<string, array<string>>
     */
    protected $words;


    /**
     * @required
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    public function getWords()
    {
        if (empty($this->words)) {
            $dir = "/src/Resources/";
            $path = $this->kernel->getProjectDir() . $dir . 'words.en.yml';
            $words = Yaml::parse(file_get_contents($path));
            $this->words = $words;
        }

        return $this->words;
    }

    /**
     * Generate a username from dictionaries of words.
     *
     * Refactor this into a service ?!
     *
     * <adjective1>_<adjective2>_<being>
     *
     * @param bool $slugCase
     * @return String
     * @throws \Exception
     */
    public function generateUsername($slugCase=false)
    {
        $words = $this->getWords();
        $adjectives = array_merge(
             $words['quantity']
            ,$words['quality']
            ,$words['size']
            ,$words['age']
            ,$words['shape']
            ,$words['color']
            ,$words['origin']
            ,$words['material']
            ,$words['qualifier']
        );

        $beings = $words['being'];

        $a1 = $a2 = '';
        $i = 0; // BAD. pop out of $adjectives instead ?
        while ($a1 == $a2 && $i < 42) {
            $i1 = array_rand($adjectives);
            $i2 = array_rand($adjectives);
            $a1 = $adjectives[min($i1, $i2)];
            $a2 = $adjectives[max($i1, $i2)];
            $i++;
        }

        if ($a1 == $a2) { // we have a lottery winner
            $a1 = "Incredibly";
            $a2 = "Lucky";
        }

        $b = $beings[array_rand($beings)];

        $x = random_int(0, 9);
        $y = random_int(0, 9);
        $z = random_int(0, 9);

        // About 406 billion right now
        // $nb = count($beings) * (count($adjectives) ** 2) * 1000;
        // print("Possibilities : $nb\n");

        if ($slugCase) {
            return mb_strtolower("${a1}-${a2}-${b}-${x}${y}${z}");
        }
        return "${a1} ${a2} ${b} ${x}${y}${z}";
    }

}