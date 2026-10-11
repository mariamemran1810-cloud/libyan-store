<?php

declare(strict_types=1);

namespace App\Core;

/**
 * التحقق من صحة المدخلات قبل حفظها في قاعدة البيانات.
 *
 * $errors = Validate::check($_POST, [
 *     'name'  => ['required', 'min:2', 'max:100'],
 *     'email' => ['required', 'email'],
 *     'qty'   => ['required', 'integer', 'min:1'],
 * ], ['name' => 'الاسم']);
 */
final class Validate
{
    /**
     * @param array<string,mixed> $input
     * @param array<string,array<string>> $rules
     * @param array<string,string> $labels أسماء الحقول بالعربية لرسائل الخطأ
     * @return array<string,string> قائمة الأخطاء (فارغة = كل شيء صحيح)
     */
    public static function check(array $input, array $rules, array $labels = []): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $label = $labels[$field] ?? $field;
            $value = $input[$field] ?? null;
            $stringValue = is_scalar($value) ? trim((string) $value) : '';

            foreach ($fieldRules as $rule) {
                $name = $rule;
                $argument = null;
                if (str_contains($rule, ':')) {
                    [$name, $argument] = explode(':', $rule, 2);
                }

                if ($name !== 'required' && $value === null) {
                    continue;
                }

                $error = self::apply($name, $argument, $value, $stringValue, $label, $field, $input);
                if ($error !== null) {
                    $errors[$field] = $error;
                    break; // نكتفي بخطأ واحد لكل حقل
                }
            }
        }

        return $errors;
    }

    private static function apply(
        string $rule,
        ?string $argument,
        mixed $value,
        string $stringValue,
        string $label,
        string $field,
        array $input
    ): ?string {
        switch ($rule) {
            case 'required':
                if ($stringValue === '' || $value === null || $value === false) {
                    return __('validation.required', ['field' => $label]);
                }
                return null;

            case 'email':
                if (!filter_var($stringValue, FILTER_VALIDATE_EMAIL)) {
                    return __('validation.email', ['field' => $label]);
                }
                return null;

            case 'url':
                if (!filter_var($stringValue, FILTER_VALIDATE_URL)) {
                    return __('validation.url', ['field' => $label]);
                }
                return null;

            case 'min':
                $min = (int) $argument;
                if (mb_strlen($stringValue) < $min) {
                    return __('validation.min', ['field' => $label, 'min' => (string) $min]);
                }
                return null;

            case 'max':
                $max = (int) $argument;
                if (mb_strlen($stringValue) > $max) {
                    return __('validation.max', ['field' => $label, 'max' => (string) $max]);
                }
                return null;

            case 'numeric':
                if (!is_numeric($stringValue)) {
                    return __('validation.numeric', ['field' => $label]);
                }
                return null;

            case 'integer':
                if (!preg_match('/^-?\d+$/', $stringValue)) {
                    return __('validation.integer', ['field' => $label]);
                }
                return null;

            case 'min_value':
                if (is_numeric($stringValue) && (float) $stringValue < (float) $argument) {
                    return __('validation.min_value', ['field' => $label, 'min' => (string) $argument]);
                }
                return null;

            case 'max_value':
                if (is_numeric($stringValue) && (float) $stringValue > (float) $argument) {
                    return __('validation.max_value', ['field' => $label, 'max' => (string) $argument]);
                }
                return null;

            case 'in':
                $allowed = explode(',', (string) $argument);
                if (!in_array($stringValue, $allowed, true)) {
                    return __('validation.in', ['field' => $label]);
                }
                return null;

            case 'confirmed':
                $other = (string) ($input[$field . '_confirmation'] ?? '');
                if ($stringValue !== $other) {
                    return __('validation.confirmed', ['field' => $label]);
                }
                return null;

            case 'slug':
                if (!preg_match('/^[a-z0-9\-]{2,120}$/', $stringValue)) {
                    return __('validation.slug', ['field' => $label]);
                }
                return null;

            case 'date':
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $stringValue) || !strtotime($stringValue)) {
                    return __('validation.date', ['field' => $label]);
                }
                return null;

            case 'boolean':
                if (!in_array($stringValue, ['0', '1', 'true', 'false'], true)) {
                    return __('validation.boolean', ['field' => $label]);
                }
                return null;
        }

        return null;
    }
}