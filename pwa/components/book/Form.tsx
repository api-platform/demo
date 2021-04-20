import { FunctionComponent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import { ErrorMessage, Formik } from "formik";
import { fetch } from "utils/dataAccess";
import { Book } from "types/Book";

interface Props {
  book?: Book;
}

export const Form: FunctionComponent<Props> = ({ book }) => {
  const router = useRouter();

  return (
    <div>
      <h1>{book ? `Edit Book ${book["@id"]}` : `Create Book`}</h1>
      <Formik
        initialValues={book ? { ...book } : new Book()}
        validate={(values) => {
          const errors = {};
          // add your validation logic here
          return errors;
        }}
        onSubmit={async (values, { setSubmitting, setStatus, setErrors }) => {
          const isCreation = !values["@id"];
          try {
            await fetch(isCreation ? "/books" : values["@id"], {
              method: isCreation ? "POST" : "PUT",
              body: JSON.stringify(values),
            });
            setStatus({
              isValid: true,
              msg: `Element ${isCreation ? "created" : "updated"}.`,
            });
            router.push("/books");
          } catch (error) {
            setStatus({
              isValid: false,
              msg: `${error.defaultErrorMsg}`,
            });
            setErrors(error.fields);
          }
          setSubmitting(false);
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
              <label className="form-control-label" htmlFor="_isbn">
                isbn
              </label>
              <input
                name="isbn"
                id="_isbn"
                value={values.isbn ?? ""}
                type="text"
                placeholder="The ISBN of the book"
                className={`form-control${
                  errors.isbn && touched.isbn ? " is-invalid" : ""
                }`}
                aria-invalid={!!(errors.isbn && touched.isbn)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage className="text-danger" component="div" name="isbn" />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_title">
                title
              </label>
              <input
                name="title"
                id="_title"
                value={values.title ?? ""}
                type="text"
                placeholder="The title of the book"
                className={`form-control${
                  errors.title && touched.title ? " is-invalid" : ""
                }`}
                aria-invalid={!!(errors.title && touched.title)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="title"
            />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_description">
                description
              </label>
              <input
                name="description"
                id="_description"
                value={values.description ?? ""}
                type="text"
                placeholder="A description of the item"
                className={`form-control${
                  errors.description && touched.description ? " is-invalid" : ""
                }`}
                aria-invalid={!!(errors.description && touched.description)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="description"
            />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_author">
                author
              </label>
              <input
                name="author"
                id="_author"
                value={values.author ?? ""}
                type="text"
                placeholder="The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably"
                className={`form-control${
                  errors.author && touched.author ? " is-invalid" : ""
                }`}
                aria-invalid={!!(errors.author && touched.author)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="author"
            />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_publicationDate">
                publicationDate
              </label>
              <input
                name="publicationDate"
                id="_publicationDate"
                value={values.publicationDate ?? ""}
                type="text"
                placeholder="The date on which the CreativeWork was created or the item was added to a DataFeed"
                className={`form-control${
                  errors.publicationDate && touched.publicationDate
                    ? " is-invalid"
                    : ""
                }`}
                aria-invalid={!!(errors.publicationDate && touched.publicationDate)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="publicationDate"
            />
            <div className="form-group">
              <label className="form-control-label" htmlFor="_reviews">
                reviews
              </label>
              <input
                name="reviews"
                id="_reviews"
                value={values.reviews ?? ""}
                type="text"
                placeholder="The book's reviews"
                className={`form-control${
                  errors.reviews && touched.reviews ? " is-invalid" : ""
                }`}
                aria-invalid={!!(errors.reviews && touched.reviews)}
                onChange={handleChange}
                onBlur={handleBlur}
              />
            </div>
            <ErrorMessage
              className="text-danger"
              component="div"
              name="reviews"
            />

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
    </div>
  );
};
