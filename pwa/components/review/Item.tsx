import {type FunctionComponent, useState} from "react";
import {useMutation} from "@tanstack/react-query";
import Rating from "@mui/material/Rating";

import {Error} from "../common/Error";
import {type Review} from "../../types/Review";
import {fetchApi} from "../../utils/dataAccess";
import {Form} from "./Form";
import {usePermission} from "../../utils/review";
import {useAccessToken} from "../../hooks/useAuth";

interface Props {
  review: Review;
  onDelete?: (review: Review) => void;
  onEdit?: (review: Review) => void;
}

interface DeleteParams {
  id: string;
}

const deleteReview = async (id: string, accessToken: string|null) => await fetchApi<Review>(id, { method: "DELETE" }, accessToken);

export const Item: FunctionComponent<Props> = ({ review, onDelete }) => {
  const { accessToken, session } = useAccessToken();
  const [data, setData] = useState<Review>(review);
  const [error, setError] = useState<string | undefined>();
  const [edit, setEdit] = useState<boolean>(false);
  const isGranted = usePermission(data, accessToken);

  const deleteMutation = useMutation({
    mutationFn: async ({ id }: DeleteParams) => deleteReview(id, accessToken),
    onSuccess: () => {
      if (onDelete) {
        onDelete(data);
      }
    },
    onError: (error) => {
      setError(`Error when deleting the resource: ${error}`);
      console.error(error);
    },
  });

  const handleDelete = (review: Review) => {
    if (!review || !review["@id"]) return;
    if (!window.confirm("Are you sure you want to delete this item?")) return;
    deleteMutation.mutate({ id: review["@id"] });
  };

  return (
    <>
      {!!error && (
        <Error message={error}/>
      )}
      <div key={data["@id"]} className="mb-5 flex" data-testid="review">
        <div className="font-semibold text-gray-600 text-xl w-[50px] h-[50px] px-3 py-1 mr-3 rounded-full bg-gray-200 flex items-center justify-center">
          {data.user?.name?.substring(0, 1) ?? "John Doe"}
        </div>
        <div className="w-full">
          {edit && (
            <Form book={data.book} username={session?.user?.name ?? "John Doe"} review={data}
                  onSuccess={(values) => {
                    setData({
                      ...data,
                      rating: Number(values.rating),
                      body: values.body,
                    });
                    setEdit(!edit);
                  }}
            />
          ) || (
            <>
              <p>
                <span className="text-lg font-semibold">{data.user?.name ?? "John Doe"}</span>
                <span className="text-xs text-gray-400 ml-3">
                  <span className="mr-2">・</span>
                  {new Date(data["publishedAt"]).toLocaleDateString()}
                </span>
                <Rating value={Number(data["rating"] ?? 0)} readOnly className="ml-2" size="small"/>
              </p>
              <p className="mt-2 mb-2 text-justify">{data["body"]}</p>
              {isGranted && (
                <div className="text-xs text-gray-400">
                  <a href="#" className="mr-1.5 text-gray-400 hover:underline" onClick={(e) => {
                    e.preventDefault();
                    setEdit(!edit);
                  }}>Edit</a>・
                  <a href="#" className="ml-1.5 text-gray-400 hover:underline" onClick={(e) => {
                    e.preventDefault();
                    handleDelete(data);
                  }}>Delete</a>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </>
  );
}
