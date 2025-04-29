import { trans, getLocale, setLocale, setLocaleFallbacks, throwWhenNotFound } from '@symfony/ux-translator';

setLocaleFallbacks({
    'pt_br': 'pt-br',
    'regmel': 'regmel',
    'en': 'en',
    'es': 'es',
});
throwWhenNotFound(true);

export { trans };

export * from '@app/translations';
