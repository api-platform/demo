import { type ReactNode, useState } from "react";
import {
  type DehydratedState,
  Hydrate,
  QueryClient,
  QueryClientProvider,
} from "react-query";

import { Header } from "@/components/common/Header";

export const Layout = ({
  children,
  dehydratedState,
}: {
  children: ReactNode;
  dehydratedState: DehydratedState;
}) => {
  const [queryClient] = useState(() => new QueryClient());

  return (
    <>
      <QueryClientProvider client={queryClient}>
        <Hydrate state={dehydratedState}>
          <Header/>
          {children}
        </Hydrate>
      </QueryClientProvider>
    </>
  );
};
