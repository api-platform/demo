import { createContext } from 'react';

const DocContext = createContext({
  docType: 'hydra',
  setDocType: (_docType: string) => {},
});

export default DocContext;
