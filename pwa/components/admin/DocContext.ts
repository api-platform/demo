import { createContext } from 'react';

const DocContext = createContext({
  docType: 'hydra',
  openApiEntrypoint: '',
  openApiDocEntrypoint: '',
  setDocType: (_docType: string) => {},
  setOpenApiEntrypoint: (_entrypoint: string) => {},
  setOpenApiDocEntrypoint: (_docEntrypoint: string) => {},
});

export default DocContext;
