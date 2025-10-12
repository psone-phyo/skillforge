import { usePage } from '@inertiajs/vue3';

type Replacements = Record<string, string | number>;

function interpolate(str: string, replacements: Replacements = {}) {
  return Object.keys(replacements).reduce((s, key) => {
    return s.replace(new RegExp(':' + key, 'g'), String(replacements[key]));
  }, str);
}

export function useI18n() {
  const page = usePage();
  // messages.php content shared from backend
  const dict = (page.props.translations as any) || {};
  const locale = (page.props.locale as string) || 'en';

  // t('nav.home'), t('common.hello_user', { name: 'Alex' })
  const t = (path: string, replacements: Replacements = {}) => {
    const segs = path.split('.');
    let cur: any = dict;
    for (const k of segs) {
      cur = cur?.[k];
      if (cur === undefined || cur === null) break;
    }
    const out = typeof cur === 'string' ? cur : path; // fallback to key
    return interpolate(out, replacements);
  };

  return { t, locale, dict };
}   
