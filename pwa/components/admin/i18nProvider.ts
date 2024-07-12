import { resolveBrowserLocale } from "react-admin";
import polyglotI18nProvider from "ra-i18n-polyglot";
import englishMessages from "ra-language-english";
import frenchMessages from "ra-language-french";

const messages = {
  fr: frenchMessages,
  en: englishMessages,
};
const i18nProvider = polyglotI18nProvider(
  // @ts-ignore
  (locale) => (messages[locale] ? messages[locale] : messages.en),
  resolveBrowserLocale()
);

export default i18nProvider;
