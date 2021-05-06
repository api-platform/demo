import get from "lodash/get";
import has from "lodash/has";
import mapValues from "lodash/mapValues";
import isomorphicFetch from "isomorphic-unfetch";
import { NEXT_PUBLIC_ENTRYPOINT } from "config/entrypoint";
// Firefox doesn't seem to properly support EventSource
import { NativeEventSource, EventSourcePolyfill } from "event-source-polyfill";

const MIME_TYPE = "application/ld+json";

interface Violation {
  message: string;
  propertyPath: string;
}

const extractHubURL = function (response) {
  const linkHeader = response.headers.get('Link');
  if (!linkHeader) return null;

  const matches = linkHeader.match(
    /<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/
  );

  return matches && matches[1] ? new URL(matches[1], NEXT_PUBLIC_ENTRYPOINT) : null;
}

export const fetch = async (id: string, init: RequestInit = {}) => {
  if (typeof init.headers === "undefined") init.headers = {};
  if (!init.headers.hasOwnProperty("Accept"))
    init.headers = { ...init.headers, Accept: MIME_TYPE };
  if (
    init.body !== undefined &&
    !(init.body instanceof FormData) &&
    !init.headers.hasOwnProperty("Content-Type")
  )
    init.headers = { ...init.headers, "Content-Type": MIME_TYPE };

  const resp = await isomorphicFetch(NEXT_PUBLIC_ENTRYPOINT + id, init);
  if (resp.status === 204) return;

  const json = await resp.json();
  if (resp.ok) {
    return {
      hubURL: extractHubURL(resp).toString(), // URL cannot be serialized as JSON, must be sent as string
      data: normalize(json),
    };
  }

  const defaultErrorMsg = json["hydra:title"];
  const status = json["hydra:description"] || resp.statusText;
  if (!json.violations) throw Error(defaultErrorMsg);
  const fields = {};
  json.violations.map(
    (violation: Violation) =>
      (fields[violation.propertyPath] = violation.message)
  );

  throw { defaultErrorMsg, status, fields };
};

export const normalize = (data: any) => {
  if (has(data, "hydra:member")) {
    // Normalize items in collections
    data["hydra:member"] = data["hydra:member"].map((item: any) =>
      normalize(item)
    );

    return data;
  }

  // Flatten nested documents
  return mapValues(data, (value) =>
    Array.isArray(value)
      ? value.map((v) => get(v, "@id", v))
      : get(value, "@id", value)
  );
};

export function mercureSubscribe(url, topics) {
  topics.forEach(topic => url.searchParams.append('topic', new URL(topic, NEXT_PUBLIC_ENTRYPOINT)));

  const EventSource = NativeEventSource || EventSourcePolyfill;

  return new EventSource(url.toString());
}
