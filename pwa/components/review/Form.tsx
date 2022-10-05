import { FunctionComponent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import { ErrorMessage, Formik } from "formik";
import { useMutation } from "react-query";

import { fetch, FetchError, FetchResponse } from "../../utils/dataAccess";
import { Review } from "../../types/Review";

interface Props {
  review?: Review;
}

interface SaveParams {
  values: Review;
}

interface DeleteParams {
  id: string;
}

const saveReview = async ({ values }: SaveParams) =>
  await fetch<Review>(!values["@id"] ? "/reviews" : values["@id"], {
    method: !values["@id"] ? "POST" : "PUT",
    body: JSON.stringify(values),
  });

const deleteReview = async (id: string) =>
  await fetch<Review>(id, { method: "DELETE" });

export const Form: FunctionComponent<Props> = ({ review }) => {
  const [, setError] = useState<string | null>(null);
  const router = useRouter();

  const saveMutation = useMutation<
    FetchResponse<Review> | undefined,
    Error | FetchError,
    SaveParams
  >((saveParams) => saveReview(saveParams));

  const deleteMutation = useMutation<
    FetchResponse<Review> | undefined,
    Error | FetchError,
    DeleteParams
  >(({ id }) => deleteReview(id), {
    onSuccess: () => {
      router.push("/reviews");
    },
    onError: (error) => {
      setError(`Error when deleting the resource: ${error}`);
      console.error(error);
    },
  });

  const handleDelete = () => {
    if (!review || !review["@id"]) return;
    if (!window.confirm("Are you sure you want to delete this item?")) return;
    deleteMutation.mutate({ id: review["@id"] });
  };

  return (
    <div>
      <h1>{review ? `Edit Review ${review["@id"]}` : `Create Review`}</h1>
      <Formik
        initialValues={
          review
            ? {
                ...review,
                book: review["book"]?.["@id"] ?? "",
              }
            : new Review()
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
                router.push("/reviews");
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
              <label className="form-control-label" htmlFor="review_body">
                body
              </label>
              <input
                name="body"
                id="review_body"
                value={values.body ?? ""}
                type="text"
                placeholder="The actual body of the review."
                required={true}
                className={`form-control${
                  errors.body && touched.body ? " is-invalid" : ""
                }`}
                aria-invalid={errors.body && touched.body ? "true" : undefined}
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="invalid-feedback"
                component="div"
                name="body"
              />
            </div>
            <div className="form-group">
              <label className="form-control-label" htmlFor="review_rating">
                rating
              </label>
              <input
                name="rating"
                id="review_rating"
                value={values.rating ?? ""}
                type="number"
                placeholder="A rating."
                required={true}
                className={`form-control${
                  errors.rating && touched.rating ? " is-invalid" : ""
                }`}
                aria-invalid={
                  errors.rating && touched.rating ? "true" : undefined
                }
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="invalid-feedback"
                component="div"
                name="rating"
              />
            </div>
            <div className="form-group">
              <label className="form-control-label" htmlFor="review_book">
                book
              </label>
              <input
                name="book"
                id="review_book"
                value={values.book ?? ""}
                type="text"
                placeholder="The item that is being reviewed/rated."
                required={true}
                className={`form-control${
                  errors.book && touched.book ? " is-invalid" : ""
                }`}
                aria-invalid={errors.book && touched.book ? "true" : undefined}
                onChange={handleChange}
                onBlur={handleBlur}
              />
              <ErrorMessage
                className="invalid-feedback"
                component="div"
                name="book"
              />
            </div>
            <div className="form-group">
              <label className="form-control-label" htmlFor="review_author">
                author
              </label>
              <input
                name="author"
                id="review_author"
                value={values.author ?? ""}
                type="text"
                placeholder="The author of the review."
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
                htmlFor="review_publicationDate"
              >
                publicationDate
              </label>
              <input
                name="publicationDate"
                id="review_publicationDate"
                value={values.publicationDate?.toLocaleString() ?? ""}
                type="dateTime"
                placeholder="Publication date of the review."
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
      <Link href="/reviews">
        <a className="btn btn-primary">Back to list</a>
      </Link>
      {review && (
        <button className="btn btn-danger" onClick={handleDelete}>
          <a>Delete</a>
        </button>
      )}
    </div>
  );
};
