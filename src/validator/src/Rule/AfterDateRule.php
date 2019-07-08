<?php declare(strict_types=1);

namespace Swoft\Validator\Rule;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Validator\Annotation\Mapping\AfterDate;
use Swoft\Validator\Contract\RuleInterface;
use Swoft\Validator\Exception\ValidatorException;

/**
 * Class AfterDateRule
 *
 * @since 2.0
 *
 * @Bean(AfterDate::class)
 */
class AfterDateRule implements RuleInterface
{
    /**
     * @param array $data
     * @param string $propertyName
     * @param object $item
     * @param null $default
     *
     * @return array
     * @throws ValidatorException
     */
    public function validate(array $data, string $propertyName, $item, $default = null): array
    {
        /* @var AfterDate $item */
        $date = $item->getDate();
        $value = $data[$propertyName];
        if (strtotime($value) >= strtotime($date)) {
            return $data;
        }

        $message = $item->getMessage();
        $message = (empty($message)) ? sprintf('%s must be after %s', $propertyName, $date) : $message;

        throw new ValidatorException($message);
    }
}