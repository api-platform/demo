import { FunctionComponent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import { ArrayHelpers, ErrorMessage, Field, FieldArray, Formik } from "formik";
import { useMutation } from "react-query";

import { fetch, FetchError, FetchResponse } from "../../utils/dataAccess";
import { Book } from "../../types/Book";

interface Props {
  book?: Book;
}

interface SaveParams {
  values: Book;
}

interface DeleteParams {
  id: string;
}

const saveBook = async ({ values }: SaveParams) =>
  await fetch<Book>(!values["@id"] ? "/books" : values["@id"], {
    method: !values["@id"] ? "POST" : "PUT",
    body: JSON.stringify(values),
  });

const deleteBook = async (id: string) =>
  await fetch<Book>(id, { method: "DELETE" });

export const Form: FunctionComponent<Props> = ({ book }) => {
  const [, setError] = useState<string | null>(null);
  const router = useRouter();

  const saveMutation = useMutation<
    FetchResponse<Book> | undefined,
    Error | FetchError,
    SaveParams
  >((saveParams) => saveBook(saveParams));

  const deleteMutation = useMutation<
    FetchResponse<Book> | undefined,
    Error | FetchError,
    DeleteParams
  >(({ id }) => deleteBook(id), {
    onSuccess: () => {
      router.push("/books");
    },
    onError: (error) => {
      setError(`Error when deleting the resource: ${error}`);
      console.error(error);
    },
  });

  const handleDelete = () => {
    if (!book || !book["@id"]) return;
    if (!window.confirm("Are you sure you want to delete this item?")) return;
    deleteMutation.mutate({ id: book["@id"] });
  };

  return (
    <div className="container mx-auto px-4 max-w-2xl mt-4">
      <Link
        href="/books"
        className="text-sm text-cyan-500 font-bold hover:text-cyan-700"
      >
        {`< Back to list`}
      </Link>
      <h1 className="text-3xl my-2">
        {book ? `Edit Book ${book["@id"]}` : `Create Book`}
      </h1>
      <Formik
        initialValues={
          book
            ? {
                ...book,
                reviews: book["reviews"]?.map((emb: any) => emb["@id"]) ?? [],
              }
            : new Book()
        }
        validate={() => {
          const errors = {};
          // add your validation logic here
          return errors;
        }}
        onSubmit={(values, { setSubmitting, setStatus, setErrors }) => {
          const isCreation = !values["@id"];
          saveMutation.mutate(
            { values },
            {
              onSuccess: () => {
                setStatus({
                  isValid: true,
                  msg: `Element ${isCreation ? "created" : "updated"}.`,
                });
                router.push("/books");
              },
              onError: (error) => {
                setStatus({
                  isValid: false,
                  msg: `${error.message}`,
                });
                if ("fields" in error) {
                  setErrors(error.fields);
                }
              },
              onSettled: () => {
                setSubmitting(false);
              },
            }
          );
        }}
      >
        {({
          values,
          status,
          errors,
          touched,
          handleChange,
          handleBlur,
          handleSubmit,
          isSubmitting,
        }) => (
          <form className="shadow-md p-4" onSubmit={handleSubmit}>
            <div className="mb-2">
              <label
                className="text-gray-700 block text-sm font-bold"
                htmlFor="book_isbn"
              >
                isbn
              </label>
              <input
                name="isbn"
                id="book_isbn"
                value={values.isbn ?? ""}
                type="text"
                placeholder="The ISBN of the book."
                className={`mt-1 block w-full ${
                  errors.isbn && touched.isbn ? "border-red-500" : ""
                }`}
                aria-invalid={errors.isbn && touched.isbn ? "true" : undefined}
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="text-xs text-red-500 pt-1"
                component="div"
                name="isbn"
              />
            </div>
            <div className="mb-2">
              <label
                className="text-gray-700 block text-sm font-bold"
                htmlFor="book_title"
              >
                title
              </label>
              <input
                name="title"
                id="book_title"
                value={values.title ?? ""}
                type="text"
                placeholder="The title of the book."
                required={true}
                className={`mt-1 block w-full ${
                  errors.title && touched.title ? "border-red-500" : ""
                }`}
                aria-invalid={
                  errors.title && touched.title ? "true" : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="text-xs text-red-500 pt-1"
                component="div"
                name="title"
              />
            </div>
            <div className="mb-2">
              <label
                className="text-gray-700 block text-sm font-bold"
                htmlFor="book_description"
              >
                description
              </label>
              <input
                name="description"
                id="book_description"
                value={values.description ?? ""}
                type="text"
                placeholder="A description of the item."
                required={true}
                className={`mt-1 block w-full ${
                  errors.description && touched.description
                    ? "border-red-500"
                    : ""
                }`}
                aria-invalid={
                  errors.description && touched.description ? "true" : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="text-xs text-red-500 pt-1"
                component="div"
                name="description"
              />
            </div>
            <div className="mb-2">
              <label
                className="text-gray-700 block text-sm font-bold"
                htmlFor="book_author"
              >
                author
              </label>
              <input
                name="author"
                id="book_author"
                value={values.author ?? ""}
                type="text"
                placeholder="The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably."
                required={true}
                className={`mt-1 block w-full ${
                  errors.author && touched.author ? "border-red-500" : ""
                }`}
                aria-invalid={
                  errors.author && touched.author ? "true" : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="text-xs text-red-500 pt-1"
                component="div"
                name="author"
              />
            </div>
            <div className="mb-2">
              <label
                className="text-gray-700 block text-sm font-bold"
                htmlFor="book_publicationDate"
              >
                publicationDate
              </label>
              <input
                name="publicationDate"
                id="book_publicationDate"
                value={values.publicationDate?.toLocaleString() ?? ""}
                type="dateTime"
                placeholder="The date on which the CreativeWork was created or the item was added to a DataFeed."
                required={true}
                className={`mt-1 block w-full ${
                  errors.publicationDate && touched.publicationDate
                    ? "border-red-500"
                    : ""
                }`}
                aria-invalid={
                  errors.publicationDate && touched.publicationDate
                    ? "true"
                    : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="text-xs text-red-500 pt-1"
                component="div"
                name="publicationDate"
              />
            </div>
            <div className="mb-2">
              <div className="text-gray-700 block text-sm font-bold">
                reviews
              </div>
              <FieldArray
                name="reviews"
                render={(arrayHelpers: ArrayHelpers) => (
                  <div className="mb-2" id="book_reviews">
                    {values.reviews && values.reviews.length > 0 ? (
                      values.reviews.map((item: any, index: number) => (
                        <div key={index}>
                          <Field name={`reviews.${index}`} />
                          <button
                            type="button"
                            onClick={() => arrayHelpers.remove(index)}
                          >
                            -
                          </button>
                          <button
                            type="button"
                            onClick={() => arrayHelpers.insert(index, "")}
                          >
                            +
                          </button>
                        </div>
                      ))
                    ) : (
                      <button
                        type="button"
                        onClick={() => arrayHelpers.push("")}
                      >
                        Add
                      </button>
                    )}
                  </div>
                )}
              />
            </div>
            {status && status.msg && (
              <div
                className={`border px-4 py-3 my-4 rounded ${
                  status.isValid
                    ? "text-cyan-700 border-cyan-500 bg-cyan-200/50"
                    : "text-red-700 border-red-400 bg-red-100"
                }`}
                role="alert"
              >
                {status.msg}
              </div>
            )}
            <button
              type="submit"
              className="inline-block mt-2 bg-cyan-500 hover:bg-cyan-700 text-sm text-white font-bold py-2 px-4 rounded"
              disabled={isSubmitting}
            >
              Submit
            </button>
          </form>
        )}
      </Formik>
      <div className="flex space-x-2 mt-4 justify-end">
        {book && (
          <button
            className="inline-block mt-2 border-2 border-red-400 hover:border-red-700 hover:text-red-700 text-sm text-red-400 font-bold py-2 px-4 rounded"
            onClick={handleDelete}
          >
            Delete
          </button>
        )}
      </div>
    </div>
  );
};
