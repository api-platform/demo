import type { Metadata } from "next";
import { type ReactNode } from "react";
import { SessionProvider } from "next-auth/react";
import "@fontsource/poppins";
import "@fontsource/poppins/600.css";
import "@fontsource/poppins/700.css";

import { Layout } from "../components/common/Layout";
import "../styles/globals.css";
import { Providers } from "./providers";
import { auth } from "./auth";

export const metadata: Metadata = {
  title: 'Welcome to API Platform!',
}
export default async function RootLayout({ children }: { children: ReactNode }) {
  const session = await auth();

  return (
    <html lang="en">
      <body>
        <SessionProvider session={session}>
          <Providers>
            <Layout>
              {children}
            </Layout>
          </Providers>
        </SessionProvider>
      </body>
    </html>
  );
};
