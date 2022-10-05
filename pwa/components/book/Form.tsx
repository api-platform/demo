import { FunctionComponent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import { ErrorMessage, Field, FieldArray, Formik } from "formik";
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
    <div>
      <h1>{book ? `Edit Book ${book["@id"]}` : `Create Book`}</h1>
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
          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label className="form-control-label" htmlFor="book_isbn">
                isbn
              </label>
              <input
                name="isbn"
                id="book_isbn"
                value={values.isbn ?? ""}
                type="text"
                placeholder="The ISBN of the book."
                className={`form-control${
                  errors.isbn && touched.isbn ? " is-invalid" : ""
                }`}
                aria-invalid={errors.isbn && touched.isbn ? "true" : undefined}
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="invalid-feedback"
                component="div"
                name="isbn"
              />
            </div>
            <div className="form-group">
              <label className="form-control-label" htmlFor="book_title">
                title
              </label>
              <input
                name="title"
                id="book_title"
                value={values.title ?? ""}
                type="text"
                placeholder="The title of the book."
                required={true}
                className={`form-control${
                  errors.title && touched.title ? " is-invalid" : ""
                }`}
                aria-invalid={
                  errors.title && touched.title ? "true" : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="invalid-feedback"
                component="div"
                name="title"
              />
            </div>
            <div className="form-group">
              <label className="form-control-label" htmlFor="book_description">
                description
              </label>
              <input
                name="description"
                id="book_description"
                value={values.description ?? ""}
                type="text"
                placeholder="A description of the item."
                required={true}
                className={`form-control${
                  errors.description && touched.description ? " is-invalid" : ""
                }`}
                aria-invalid={
                  errors.description && touched.description ? "true" : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="invalid-feedback"
                component="div"
                name="description"
              />
            </div>
            <div className="form-group">
              <label className="form-control-label" htmlFor="book_author">
                author
              </label>
              <input
                name="author"
                id="book_author"
                value={values.author ?? ""}
                type="text"
                placeholder="The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably."
                required={true}
                className={`form-control${
                  errors.author && touched.author ? " is-invalid" : ""
                }`}
                aria-invalid={
                  errors.author && touched.author ? "true" : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="invalid-feedback"
                component="div"
                name="author"
              />
            </div>
            <div className="form-group">
              <label
                className="form-control-label"
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
                className={`form-control${
                  errors.publicationDate && touched.publicationDate
                    ? " is-invalid"
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
                className="invalid-feedback"
                component="div"
                name="publicationDate"
              />
            </div>
            <div className="form-group">
              <div className="form-control-label">reviews</div>
              <FieldArray
                name="reviews"
                render={(arrayHelpers) => (
                  <div id="book_reviews">
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
                className={`alert ${
                  status.isValid ? "alert-success" : "alert-danger"
                }`}
                role="alert"
              >
                {status.msg}
              </div>
            )}
            <button
              type="submit"
              className="btn btn-success"
              disabled={isSubmitting}
            >
              Submit
            </button>
          </form>
        )}
      </Formik>
      <Link href="/books">
        <a className="btn btn-primary">Back to list</a>
      </Link>
      {book && (
        <button className="btn btn-danger" onClick={handleDelete}>
          <a>Delete</a>
        </button>
      )}
    </div>
  );
};
