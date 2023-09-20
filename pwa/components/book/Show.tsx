import { FunctionComponent, useState } from "react";
import Image from 'next/image';
import Link from "next/link";
import { useRouter } from "next/router";
import Head from "next/head";
import { useSession, signIn } from "next-auth/react";

import ReferenceLinks from "../common/ReferenceLinks";
import { fetch, getItemPath } from "../../utils/dataAccess";
import { Book } from "../../types/Book";
import SyncLoader from "react-spinners/SyncLoader";

interface Props {
  book: Book;
  text: string;
}

export const Show: FunctionComponent<Props> = ({ book, text }) => {
  const [, setBook] = useState<Book>(book);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();
  const { data: session } = useSession();

  const handleDelete = async () => {
    if (!book["@id"]) return;
    if (!window.confirm("Are you sure you want to delete this item?")) return;

    try {
      await fetch(book["@id"], { method: "DELETE" });
      router.push("/books");
    } catch (error) {
      setError("Error when deleting the resource.");
      console.error(error);
    }
  };

  const handleGenerateCover = async () => {
    if (!book["@id"]) return;

    try {
      // Disable cover to display spinner
      delete book["cover"];
      setBook(book);
      // Display spinner
      setLoading(true);
      await fetch(`${book["@id"]}/generate-cover`, { method: "PUT" });
    } catch (error) {
      setError("Error when generating the book cover.");
      console.error(error);
    }
  };

  if (loading && book["cover"]) {
    setLoading(false);
  }

  return (
    <div className="p-4">
      <Head>
        <title>{`Show Book ${book["@id"]}`}</title>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: text }}
        />
      </Head>
      <Link
        href="/books"
        className="text-sm text-cyan-500 font-bold hover:text-cyan-700"
      >
        {"< Back to list"}
      </Link>
      <h1 className="text-3xl mb-2">{`Show Book ${book["@id"]}`}</h1>
      <table
        cellPadding={10}
        className="shadow-md table border-collapse min-w-full leading-normal table-auto text-left my-3"
      >
        <thead className="w-full text-xs uppercase font-light text-gray-700 bg-gray-200 py-2 px-4">
          <tr>
            <th>Field</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody className="text-sm divide-y divide-gray-200">
          <tr>
            <th scope="row">isbn</th>
            <td>{book["isbn"]}</td>
          </tr>
          <tr>
            <th scope="row">title</th>
            <td>{book["title"]}</td>
          </tr>
          <tr>
            <th scope="row">description</th>
            <td>{book["description"]}</td>
          </tr>
          <tr>
            <th scope="row">author</th>
            <td>{book["author"]}</td>
          </tr>
          <tr>
            <th scope="row">publicationDate</th>
            <td>{book["publicationDate"]?.toLocaleString()}</td>
          </tr>
          <tr>
            <th scope="row">reviews</th>
            <td>
              {book["reviews"] && (
                <ReferenceLinks
                  items={book["reviews"].map((emb: any) => ({
                    href: getItemPath(emb["@id"], "/reviews/[id]"),
                    name: emb["@id"],
                  }))}
                />
              )}
            </td>
          </tr>
          <tr>
            <th scope="row">cover</th>
            <td>
              {loading && (
                <SyncLoader size={8} color="#46B6BF" />
              ) || book["cover"] && (
                <>
                  <button
                    className="inline-block mt-2 mb-2 border-2 border-blue-500 bg-blue-500 hover:border-blue-700 hover:bg-blue-700 text-xs text-white font-bold py-2 px-4 rounded"
                    onClick={handleGenerateCover}
                  >
                    Re-generate book cover
                  </button>
                  <Image
                    alt="Book cover"
                    src={book["cover"]}
                    width={200}
                    height={200}
                  />
                </>
              ) || session && (
                <button
                  className="inline-block mt-2 border-2 border-blue-500 bg-blue-500 hover:border-blue-700 hover:bg-blue-700 text-xs text-white font-bold py-2 px-4 rounded"
                  onClick={handleGenerateCover}
                >
                  Generate book cover
                </button>
              ) || (
                <button
                  className="bg-blue-500 hover:bg-blue-700 text-xs text-white font-bold py-2 px-4 rounded"
                  onClick={() => signIn('keycloak')}
                >
                  Sign in to generate the book cover
                </button>
              )}
            </td>
          </tr>
        </tbody>
      </table>
      {error && (
        <div
          className="border px-4 py-3 my-4 rounded text-red-700 border-red-400 bg-red-100"
          role="alert"
        >
          {error}
        </div>
      )}
      <div className="flex space-x-2 mt-4 items-center justify-end">
        <Link
          href={getItemPath(book["@id"], "/books/[id]/edit")}
          className="inline-block mt-2 border-2 border-cyan-500 bg-cyan-500 hover:border-cyan-700 hover:bg-cyan-700 text-xs text-white font-bold py-2 px-4 rounded"
        >
          Edit
        </Link>
        {session && (
          <button
            className="inline-block mt-2 border-2 border-red-400 hover:border-red-700 hover:text-red-700 text-xs text-red-400 font-bold py-2 px-4 rounded"
            onClick={handleDelete}
          >
            Delete
          </button>
        ) || (
          <button
            className="inline-block mt-2 border-2 border-red-400 hover:border-red-700 hover:text-red-700 text-xs text-red-400 font-bold py-2 px-4 rounded"
            onClick={() => signIn('keycloak')}
          >
            Sign in to delete the book
          </button>
        )}
      </div>
    </div>
  );
};
